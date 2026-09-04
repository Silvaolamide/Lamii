<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $conversations = Conversation::with(['userOne', 'userTwo'])
            ->where(fn ($q) => $q->where('user_one_id', $user->id)->orWhere('user_two_id', $user->id))
            ->orderByDesc('last_message_at')->get()
            ->map(fn ($conversation) => $this->conversationPayload($conversation, $user));
        return response()->json(['conversations' => $conversations]);
    }

    public function start(Request $request, User $user)
    {
        abort_if($user->id === $request->user()->id, 422);
        $me = $request->user();
        abort_unless($this->connected($me->id, $user->id) && ! $this->blocked($me->id, $user->id), 403, 'You can only chat with accepted connections.');
        [$one, $two] = collect([$me->id, $user->id])->sort()->values()->all();
        $conversation = Conversation::firstOrCreate(['user_one_id' => $one, 'user_two_id' => $two]);
        $conversation->load(['userOne', 'userTwo']);
        return response()->json(['conversation' => $this->conversationPayload($conversation, $me)]);
    }

    public function messages(Request $request, Conversation $conversation)
    {
        $this->authorizeConversation($request, $conversation);
        $after = (int) $request->integer('after', 0);
        $query = $conversation->messages()->with('sender')->oldest();
        if ($after > 0) $query->where('id', '>', $after);
        $messages = $query->limit(100)->get();
        $messages->filter(fn ($m) => $m->sender_id !== $request->user()->id && ! $m->read_at)->each(fn ($m) => $m->update(['read_at' => now()]));
        return response()->json([
            'messages' => $messages->map(fn ($m) => $this->messagePayload($m))->values(),
            'unread_count' => $this->unreadMessageCount($request->user()->id),
        ]);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $this->authorizeConversation($request, $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $message = $conversation->messages()->create(['sender_id' => $request->user()->id, 'body' => trim($data['body'])]);
        $conversation->update(['last_message_at' => now()]);
        $message->load('sender');
        $recipientId = $message->sender_id === $conversation->user_one_id ? $conversation->user_two_id : $conversation->user_one_id;
        User::find($recipientId)?->notify(new NewMessageNotification($message));
        broadcast(new MessageSent($message))->toOthers();
        return response()->json(['message' => $this->messagePayload($message)], 201);
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['count' => $this->unreadMessageCount($request->user()->id)]);
    }

    private function conversationPayload(Conversation $conversation, User $user): array
    {
        $other = $conversation->otherUser($user);
        return ['id' => $conversation->id, 'last_message_at' => $conversation->last_message_at?->toIso8601String(), 'user' => ['id' => $other->id, 'name' => $other->name, 'avatar' => $other->avatar ? asset('storage/'.$other->avatar) : null, 'bio' => $other->bio]];
    }

    private function messagePayload(Message $message): array
    {
        return ['id' => $message->id, 'sender_id' => $message->sender_id, 'sender_name' => $message->sender?->name, 'body' => $message->body, 'created_at' => $message->created_at->toIso8601String()];
    }

    private function unreadMessageCount(int $userId): int
    {
        return Message::whereNull('read_at')->where('sender_id', '!=', $userId)->whereHas('conversation', fn ($q) => $q->where(fn ($q) => $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId)))->count();
    }

    private function authorizeConversation(Request $request, Conversation $conversation): void
    {
        abort_unless(in_array($request->user()->id, [$conversation->user_one_id, $conversation->user_two_id], true), 403);
        abort_unless($this->connected($conversation->user_one_id, $conversation->user_two_id) && ! $this->blocked($conversation->user_one_id, $conversation->user_two_id), 403);
    }

    private function connected(int $a, int $b): bool
    {
        return Connection::where('status', Connection::ACCEPTED)->where(fn ($q) => $q->where(fn ($q) => $q->where('sender_id', $a)->where('recipient_id', $b))->orWhere(fn ($q) => $q->where('sender_id', $b)->where('recipient_id', $a)))->exists();
    }

    private function blocked(int $a, int $b): bool
    {
        return Block::where(fn ($q) => $q->where('blocker_id', $a)->where('blocked_id', $b))->orWhere(fn ($q) => $q->where('blocker_id', $b)->where('blocked_id', $a))->exists();
    }
}
