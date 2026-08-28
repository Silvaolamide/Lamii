<?php

namespace App\Http\Controllers;

use App\Models\Block;
use App\Models\Connection;
use App\Models\User;
use App\Notifications\NewWaveNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConnectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('connections.index', [
            'incoming' => Connection::with('sender')
                ->where('recipient_id', $user->id)
                ->where('status', Connection::PENDING)
                ->latest()
                ->get(),
            'outgoing' => Connection::with('recipient')
                ->where('sender_id', $user->id)
                ->where('status', Connection::PENDING)
                ->latest()
                ->get(),
            'accepted' => Connection::with(['sender', 'recipient'])
                ->where('status', Connection::ACCEPTED)
                ->where(fn ($query) => $query->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
                ->latest('responded_at')
                ->get(),
        ]);
    }

    public function store(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'You cannot wave at yourself.');

        $me = $request->user();

        abort_if(
            Block::where(fn ($query) => $query
                ->where(fn ($q) => $q->where('blocker_id', $me->id)->where('blocked_id', $user->id))
                ->orWhere(fn ($q) => $q->where('blocker_id', $user->id)->where('blocked_id', $me->id)))
                ->exists(),
            403,
            'You cannot interact with this user.'
        );

        $existing = Connection::where(fn ($query) => $query
            ->where('sender_id', $me->id)->where('recipient_id', $user->id))
            ->orWhere(fn ($query) => $query
                ->where('sender_id', $user->id)->where('recipient_id', $me->id))
            ->first();

        if ($existing) {
            throw ValidationException::withMessages([
                'connection' => ['A connection already exists between you.'],
            ]);
        }

        DB::transaction(function () use ($me, $user, &$connection) {
            $connection = Connection::create([
                'sender_id' => $me->id,
                'recipient_id' => $user->id,
                'status' => Connection::PENDING,
            ]);

            $connection->load('sender');
            $user->notify(new NewWaveNotification($connection));
        });

        return response()->json(['ok' => true, 'message' => 'Wave sent!']);
    }

    public function accept(Request $request, Connection $connection)
    {
        abort_unless($connection->recipient_id === $request->user()->id && $connection->status === Connection::PENDING, 403);

        $connection->update(['status' => Connection::ACCEPTED, 'responded_at' => now()]);

        return back()->with('success', 'You are now connected.');
    }

    public function decline(Request $request, Connection $connection)
    {
        abort_unless($connection->recipient_id === $request->user()->id && $connection->status === Connection::PENDING, 403);

        $connection->update(['status' => Connection::DECLINED, 'responded_at' => now()]);

        return back()->with('success', 'Wave declined.');
    }
}
