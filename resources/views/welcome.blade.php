@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-3xl py-16 text-center">
    <p class="mb-4 font-bold uppercase tracking-[.25em] text-indigo-600">Meet people nearby</p>
    <h1 class="text-5xl font-black tracking-tight sm:text-6xl">Discover people.<br><span class="text-indigo-600">Connect naturally.</span></h1>
    <p class="mx-auto mt-6 max-w-xl text-lg text-slate-600">Lamii helps you discover and connect with people around you in real time — with privacy and control at the center.</p>
    <div class="mt-8 flex justify-center gap-3"><a href="{{ route('register') }}" class="rounded-xl bg-indigo-600 px-6 py-3 font-bold text-white shadow-sm">Join Lamii</a><a href="{{ route('login') }}" class="rounded-xl border bg-white px-6 py-3 font-bold">Log in</a></div>
</div>
@endsection
