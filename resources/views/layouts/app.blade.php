<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <title>{{ $title ?? 'Lamii' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { sans: ['Inter','ui-sans-serif','system-ui'] }, boxShadow: { glow: '0 20px 60px rgba(99,102,241,.18)' } } } };
    </script>
    <style>
        body{background:#f8fafc}.app-shell{background:radial-gradient(circle at 20% 0%,rgba(99,102,241,.12),transparent 30%),radial-gradient(circle at 90% 10%,rgba(236,72,153,.08),transparent 25%),#f8fafc}.nav-blur{backdrop-filter:blur(18px);-webkit-backdrop-filter:blur(18px)}
        .safe-bottom{padding-bottom:calc(5.5rem + env(safe-area-inset-bottom))}.tap{transition:transform .18s ease,box-shadow .18s ease}.tap:active{transform:scale(.98)}
    </style>
</head>
<body class="min-h-screen font-sans text-slate-950 antialiased">
<div class="app-shell min-h-screen">
<header class="sticky top-0 z-40 border-b border-white/70 bg-white/75 nav-blur">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 lg:px-8">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="Lamii home">
            <span class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-950 text-lg font-black text-white shadow-lg">L</span>
            <span class="text-xl font-black tracking-tight">Lamii<span class="text-indigo-600">.</span></span>
        </a>
        @auth
            <div class="flex items-center gap-2">
                <a href="{{ route('notifications.index') }}" class="hidden rounded-full px-3 py-2 text-sm font-bold text-slate-600 hover:bg-slate-100 sm:block">Notifications</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">Log out</button></form>
            </div>
        @else
            <div class="flex items-center gap-2 text-sm font-bold"><a href="{{ route('login') }}" class="px-3 py-2">Log in</a><a href="{{ route('register') }}" class="rounded-full bg-slate-950 px-4 py-2 text-white shadow-lg">Join Lamii</a></div>
        @endauth
    </div>
</header>
<main class="mx-auto max-w-6xl px-5 py-7 lg:px-8 lg:py-10 safe-bottom">
    @if ($errors->any())<div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-medium text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('success'))<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">{{ session('success') }}</div>@endif
    @yield('content')
</main>
@auth
<nav class="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200/80 bg-white/90 nav-blur" style="padding-bottom:env(safe-area-inset-bottom)">
    <div class="mx-auto grid max-w-lg grid-cols-4 px-3 py-2">
        <a href="{{ route('discover') }}" class="flex flex-col items-center gap-1 rounded-2xl py-2 text-xs font-bold text-slate-500 hover:text-indigo-600"><span class="text-lg">⌕</span><span>Discover</span></a>
        <a href="{{ route('connections.index') }}" class="flex flex-col items-center gap-1 rounded-2xl py-2 text-xs font-bold text-slate-500 hover:text-indigo-600"><span class="text-lg">♡</span><span>People</span></a>
        <a href="{{ route('chat.index') }}" class="flex flex-col items-center gap-1 rounded-2xl py-2 text-xs font-bold text-slate-500 hover:text-indigo-600"><span class="text-lg">◌</span><span>Chats</span></a>
        <a href="{{ route('notifications.index') }}" class="flex flex-col items-center gap-1 rounded-2xl py-2 text-xs font-bold text-slate-500 hover:text-indigo-600"><span class="text-lg">♢</span><span>Alerts</span></a>
    </div>
</nav>
@endauth
</div>
</body>
</html>
