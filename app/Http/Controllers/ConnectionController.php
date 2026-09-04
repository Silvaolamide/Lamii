<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Connection;
use App\Models\User;
use App\Notifications\ConnectionAcceptedNotification;
use App\Notifications\NewWaveNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConnectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('connections.index', [
            'incoming' => Connection::with('sender')->where('recipient_id', $user->id)->where('status', Connection::PENDING)->latest()->get(),
            'outgoing' => Connection::with('recipient')->where('sender_id', $user->id)->where('status', Connection::PENDING)->latest()->get(),
            'accepted' => Connection::with(['sender', 'recipient'])->where('status', Connection::ACCEPTED)->where(fn ($query) => $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))->latest('responded_at')->get(),
        ]);
    }

    public function store(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'You cannot wave at yourself.');
        $me = $request->user();

        abort_if(Block::where(fn ($query) => $query
            ->where(fn ($q) => $q->where('blocker_id', $me->id)->where('blocked_id', $user->id))
            ->orWhere(fn ($q) => $q->where('blocker_id', $user->id)->where('blocked_id', $me->id))
        )->exists(), 403, 'You cannot interact with this user.');

        $existing = Connection::where(fn ($query) => $query->where('sender_id', $me->id)->where('recipient_id', $user->id))
            ->orWhere(fn ($query) => $query->where('sender_id', $user->id)->where('recipient_id', $me->id))
            ->first();

        if ($existing) {
            if ($existing->status === Connection::ACCEPTED) {
                return response()->json(['ok' => true, 'state' => 'connected', 'message' => 'You are already connected.']);
            }
            if ($existing->status === Connection::PENDING && $existing->sender_id === $me->id) {
                return response()->json(['ok' => true, 'state' => 'sent', 'message' => 'Wave already sent.']);
            }
            if ($existing->status === Connection::PENDING) {
                return response()->json(['ok' => false, 'state' => 'incoming', 'message' => 'This person has already waved at you. Check People to respond.'], 409);
            }

            return response()->json(['ok' => false, 'state' => 'exists', 'message' => 'You have already interacted with this person.'], 409);
        }

        $connection = DB::transaction(function () use ($me, $user) {
            $connection = Connection::create(['sender_id' => $me->id, 'recipient_id' => $user->id, 'status' => Connection::PENDING]);
            $connection->load('sender');
            return $connection;
        });

        try {
            $user->notify(new NewWaveNotification($connection));
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json(['ok' => true, 'state' => 'sent', 'message' => 'Wave sent!']);
    }

    public function accept(Request $request, Connection $connection)
    {
        abort_unless($connection->recipient_id === $request->user()->id && $connection->status === Connection::PENDING, 403);
        $connection->update(['status' => Connection::ACCEPTED, 'responded_at' => now()]);
        $connection->load(['sender', 'recipient']);

        try {
            $connection->sender->notify(new ConnectionAcceptedNotification($connection));
        } catch (\Throwable $e) {
            report($e);
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'state' => 'connected', 'message' => 'You are now connected.']);
        }

        return back()->with('success', 'You are now connected.');
    }

    public function decline(Request $request, Connection $connection)
    {
        abort_unless($connection->recipient_id === $request->user()->id && $connection->status === Connection::PENDING, 403);
        $connection->update(['status' => Connection::DECLINED, 'responded_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'state' => 'declined', 'message' => 'Wave declined.']);
        }

        return back()->with('success', 'Wave declined.');
    }
}
