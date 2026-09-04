<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ConnectionController as WebConnectionController;
use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;

class ConnectionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $connections = Connection::with(['sender', 'recipient'])
            ->where(fn ($q) => $q->where('sender_id', $user->id)->orWhere('recipient_id', $user->id))
            ->latest('updated_at')->get()
            ->map(function ($connection) use ($user) {
                $other = $connection->sender_id === $user->id ? $connection->recipient : $connection->sender;
                return [
                    'id' => $connection->id,
                    'status' => $connection->status,
                    'direction' => $connection->sender_id === $user->id ? 'outgoing' : 'incoming',
                    'responded_at' => $connection->responded_at?->toIso8601String(),
                    'user' => ['id' => $other->id, 'name' => $other->name, 'avatar' => $other->avatar ? asset('storage/'.$other->avatar) : null, 'bio' => $other->bio],
                ];
            });
        return response()->json(['connections' => $connections]);
    }

    public function wave(Request $request, User $user)
    {
        return app(WebConnectionController::class)->store($request, $user);
    }

    public function accept(Request $request, Connection $connection)
    {
        abort_unless($connection->recipient_id === $request->user()->id && $connection->status === Connection::PENDING, 403);
        $connection->update(['status' => Connection::ACCEPTED, 'responded_at' => now()]);
        return response()->json(['ok' => true, 'state' => 'connected']);
    }

    public function decline(Request $request, Connection $connection)
    {
        abort_unless($connection->recipient_id === $request->user()->id && $connection->status === Connection::PENDING, 403);
        $connection->update(['status' => Connection::DECLINED, 'responded_at' => now()]);
        return response()->json(['ok' => true, 'state' => 'declined']);
    }
}
