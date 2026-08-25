@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-md"><h1 class="text-3xl font-black">Reset your password</h1><p class="mt-2 text-slate-600">Enter your email and we'll send you a reset link.</p>@if(session('status'))<div class="mt-5 rounded-xl bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>@endif<form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">@csrf<label class="block"><span class="mb-2 block text-sm font-semibold">Email</span><input name="email" type="email" required class="w-full rounded-xl border px-4 py-3"></label><button class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white">Send reset link</button></form></div>
@endsection
