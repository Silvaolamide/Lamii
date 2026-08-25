<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Lamii' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900">
<header class="border-b bg-white">
    <div class="mx-auto flex max-w-5xl items-center justify-between px-5 py-4">
        <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight">Lamii<span class="text-indigo-600">.</span></a>
        @auth
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="text-sm font-semibold text-slate-600 hover:text-slate-900">Log out</button></form>
        @else
            <div class="flex gap-4 text-sm font-semibold"><a href="{{ route('login') }}">Log in</a><a href="{{ route('register') }}">Join Lamii</a></div>
        @endauth
    </div>
</header>
<main class="mx-auto max-w-5xl px-5 py-10">@if ($errors->any())<div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif @yield('content')</main>
</body>
</html>
