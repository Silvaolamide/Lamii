@extends('layouts.app')
@section('content')
<div class="relative overflow-hidden rounded-[2rem] bg-slate-950 px-6 py-14 text-white shadow-2xl shadow-indigo-200/40 sm:px-12 sm:py-20 lg:px-20">
    <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-indigo-500/30 blur-3xl"></div><div class="absolute -bottom-28 left-10 h-72 w-72 rounded-full bg-fuchsia-500/20 blur-3xl"></div>
    <div class="relative max-w-3xl">
        <div class="mb-7 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[.18em] text-white/80">● Meet nearby · Stay in control</div>
        <h1 class="text-5xl font-black tracking-[-.04em] sm:text-7xl">People are closer<br><span class="text-indigo-300">than you think.</span></h1>
        <p class="mt-6 max-w-xl text-base leading-7 text-slate-300 sm:text-lg">Lami helps you discover interesting people around you, connect when the feeling is mutual, and chat privately — without exposing your exact location.</p>
        <div class="mt-9 flex flex-col gap-3 sm:flex-row"><a href="{{ route('register') }}" class="tap rounded-2xl bg-white px-7 py-4 text-center font-black text-slate-950 shadow-xl">Start discovering →</a><a href="{{ route('login') }}" class="tap rounded-2xl border border-white/15 bg-white/10 px-7 py-4 text-center font-black text-white">I already have an account</a></div>
    </div>
</div>
<div class="grid gap-4 py-8 sm:grid-cols-3">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-indigo-50 text-xl">⌖</div><h2 class="font-black">Nearby, not exact</h2><p class="mt-2 text-sm leading-6 text-slate-500">See approximate distance. Your coordinates stay private.</p></div>
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-pink-50 text-xl">♡</div><h2 class="font-black">Mutual connections</h2><p class="mt-2 text-sm leading-6 text-slate-500">Wave first. Chat only after both people connect.</p></div>
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"><div class="mb-5 flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-xl">✓</div><h2 class="font-black">You stay in control</h2><p class="mt-2 text-sm leading-6 text-slate-500">Choose whether you are discoverable and block or report anytime.</p></div>
</div>
@endsection
