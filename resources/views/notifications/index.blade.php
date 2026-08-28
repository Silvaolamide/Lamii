@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-7 flex items-end justify-between gap-4">
        <div>
            <p class="text-xs font-black uppercase tracking-[.2em] text-indigo-600">Notifications</p>
            <h1 class="mt-2 text-4xl font-black tracking-tight sm:text-5xl">Alerts</h1>
            <p class="mt-2 text-slate-500">Stay up to date with waves, connections and messages.</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm hover:bg-slate-50">Mark all read</button>
            </form>
        @endif
    </div>

    @if($notifications->isEmpty())
        <div class="rounded-[2rem] border border-slate-200 bg-white p-12 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-indigo-50 text-2xl">♢</div>
            <h2 class="mt-5 text-xl font-black">You're all caught up</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500">New waves, connection updates and other important activity will appear here.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $notification)
                @php
                    $data = is_array($notification->data) ? $notification->data : [];
                    $title = $data['title'] ?? match($notification->type) {
                        'App\\Notifications\\NewMessageNotification' => 'New message',
                        default => 'New activity',
                    };
                    $message = $data['message'] ?? $data['body'] ?? 'You have a new notification.';
                    $isUnread = is_null($notification->read_at);
                @endphp
                <div class="rounded-[1.5rem] border {{ $isUnread ? 'border-indigo-200 bg-indigo-50/60' : 'border-slate-200 bg-white' }} p-5 shadow-sm">
                    <div class="flex gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $isUnread ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-500' }} text-lg">
                            {{ str_contains(strtolower($title), 'message') ? '💬' : '♢' }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <h2 class="font-black text-slate-950">{{ $title }}</h2>
                                    @if($isUnread)<span class="h-2.5 w-2.5 rounded-full bg-red-500" aria-label="Unread"></span>@endif
                                </div>
                                <time class="shrink-0 text-xs font-semibold text-slate-400">{{ $notification->created_at->diffForHumans() }}</time>
                            </div>
                            <p class="mt-1 text-sm leading-6 text-slate-600">{{ $message }}</p>
                            @if($isUnread)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="mt-3">
                                    @csrf
                                    <button class="text-xs font-black text-indigo-600 hover:text-indigo-800">Mark as read</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
