<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('connections.index', [
            'incoming' => Connection::with('sender')->where('recipient_id', $user->id)->where('status', Connection::PENDING)->latest()->get(),
            'outgoing' => Connection::with('recipient')->where('sender_id', $user->id)->where('status', Connection::PENDING)->latest()->get(),
            'accepted' => Connection::with(['sender', 'recipient'])->where('status', Connection::ACCEPTED)->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))->latest('responded_at')->get(),
        ]);
    }

    public function store(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422, 'You cannot wave at yourself.');

        $me = $request->user();
        $existing = Connection::where(fn ($q) => $q->where('sender_id', $me->id)->where('recipient_id', $user->id))
            ->orWhere(fn ($q) => $q->where('sender_id', $user->id)->where('recipient_id', $me->id))->first();

        if ($existing) {
            return response()->json(['message' => 'A connection already exists between you.'], 422);
        }

        Connection::create(['sender_id' => $me->id, 'recipient_id' => $user->id, 'status' => Connection::PENDING]);

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
