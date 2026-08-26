@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="mb-8">
        <p class="text-sm font-bold text-indigo-600">STEP 2 OF 3</p>
        <h1 class="mt-2 text-3xl font-black">What are you into?</h1>
        <p class="mt-2 text-slate-600">Select the things you enjoy. Laaamiii will use your interests to help you discover better connections.</p>
    </div>

    <form method="POST" action="{{ route('onboarding.interests.save') }}">
        @csrf

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @forelse($interests as $interest)
                <label class="cursor-pointer">
                    <input
                        class="peer sr-only"
                        type="checkbox"
                        name="interests[]"
                        value="{{ $interest->id }}"
                        {{ in_array($interest->id, old('interests', auth()->user()->interests->pluck('id')->all())) ? 'checked' : '' }}
                    >
                    <span class="flex min-h-14 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-center text-sm font-semibold shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500">
                        {{ $interest->name }}
                    </span>
                </label>
            @empty
                <div class="col-span-full rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">
                    No interests are available yet. Please run <code class="font-mono font-bold">php artisan migrate</code> and refresh this page.
                </div>
            @endforelse
        </div>

        @if ($interests->isNotEmpty())
            <button type="submit" class="mt-8 w-full rounded-xl bg-indigo-600 px-4 py-3 font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Continue
            </button>
        @endif
    </form>
</div>
@endsection
