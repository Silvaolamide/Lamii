@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="font-bold text-indigo-600">LAAAMIII DISCOVER</p><h1 class="mt-2 text-4xl font-black">People around you</h1><p class="mt-2 text-slate-600">Your exact location is never shown to other people.</p></div>
        <div class="flex gap-2"><a href="{{ route('connections.index') }}" class="rounded-xl border bg-white px-5 py-3 font-bold">Connections</a><button id="refresh-location" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Find people nearby</button></div>
    </div>
    <div id="location-status" class="mb-6 rounded-2xl border bg-white p-4 text-sm text-slate-600">Laaamiii needs your permission to check who is nearby. Your location is stored temporarily and expires automatically after 15 minutes.</div>
    <div id="people" class="grid gap-4 sm:grid-cols-2"></div>
</div>

<div id="location-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 p-5" role="dialog" aria-modal="true" aria-labelledby="location-title">
    <div class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-3xl">📍</div>
        <h2 id="location-title" class="mt-5 text-center text-2xl font-black">Find people around you</h2>
        <p class="mt-3 text-center text-slate-600">Allow Laaamiii to use your location so we can show you people nearby. Your exact location will not be shown to other users.</p>
        <button id="allow-location" type="button" class="mt-6 w-full rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Allow location</button>
        <button id="cancel-location" type="button" class="mt-3 w-full rounded-xl border px-5 py-3 font-bold text-slate-700">Not now</button>
        <p id="location-modal-error" class="mt-4 hidden text-center text-sm text-red-600"></p>
    </div>
</div>

<script>
const statusBox = document.getElementById('location-status');
const peopleBox = document.getElementById('people');
const button = document.getElementById('refresh-location');
const locationModal = document.getElementById('location-modal');
const allowLocation = document.getElementById('allow-location');
const cancelLocation = document.getElementById('cancel-location');
const modalError = document.getElementById('location-modal-error');
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

function escapeHtml(value = '') { const div = document.createElement('div'); div.textContent = value; return div.innerHTML; }
function showLocationModal() { modalError.classList.add('hidden'); locationModal.classList.remove('hidden'); locationModal.classList.add('flex'); }
function hideLocationModal() { locationModal.classList.add('hidden'); locationModal.classList.remove('flex'); }

async function wave(id, button) {
    button.disabled = true;
    try {
        const r = await fetch('/connections/' + id, {method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}});
        const data = await r.json();
        if (!r.ok) throw new Error(data.message || 'Could not send wave.');
        button.textContent='👋 Wave sent';
    } catch(e) { button.disabled=false; button.textContent=e.message || 'Try again'; }
}

function locationErrorMessage(error) {
    if (error.code === 1) return 'Location permission was denied. Please allow location access for this site in your browser settings and try again.';
    if (error.code === 2) return 'Your location could not be determined. Check that Location Services are enabled on your phone and try again.';
    if (error.code === 3) return 'Location lookup timed out. Please make sure Location Services are enabled and try again.';
    return 'We could not determine your location. Please try again.';
}

async function findPeople() {
    if (!navigator.geolocation) {
        statusBox.textContent = 'Your browser does not support location services.';
        return;
    }

    if (!window.isSecureContext) {
        statusBox.textContent = 'Location access requires a secure (HTTPS) connection on mobile browsers. Open Laaamiii using HTTPS, then tap Find people nearby.';
        modalError.textContent = 'This local HTTP address cannot request your phone location. Use the HTTPS address for Laaamiii.';
        modalError.classList.remove('hidden');
        return;
    }

    hideLocationModal();
    button.disabled = true;
    button.textContent = 'Checking location…';
    statusBox.textContent = 'Requesting your location…';

    navigator.geolocation.getCurrentPosition(async position => {
        const payload = {latitude:position.coords.latitude,longitude:position.coords.longitude,accuracy:position.coords.accuracy};
        try {
            const locationResponse = await fetch('{{ route('location.update') }}', {method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
            if (!locationResponse.ok) throw new Error('Location could not be saved.');

            const response = await fetch('{{ route('discover.nearby') }}?latitude='+encodeURIComponent(payload.latitude)+'&longitude='+encodeURIComponent(payload.longitude),{headers:{'Accept':'application/json'}});
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Nearby people could not be loaded.');

            statusBox.textContent=`Showing discoverable people within ${data.radius_km} km. Their exact locations are hidden.`;
            peopleBox.innerHTML=data.people.length?data.people.map(person=>`<article class="rounded-3xl border bg-white p-5 shadow-sm"><div class="flex items-start gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 text-xl font-black text-indigo-700">${person.avatar?`<img src="${escapeHtml(person.avatar)}" class="h-full w-full object-cover" alt="">`:escapeHtml(person.name.charAt(0).toUpperCase())}</div><div class="min-w-0 flex-1"><h2 class="font-bold text-slate-900">${escapeHtml(person.name)}</h2><p class="mt-1 font-semibold text-indigo-600">📍 ${escapeHtml(person.distance)}</p><p class="mt-2 text-sm text-slate-600">${escapeHtml(person.bio||'New to Laaamiii')}</p><button onclick="wave(${person.id},this)" class="mt-4 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">👋 Wave</button></div></div></article>`).join(''):'<div class="rounded-3xl border bg-white p-8 text-center text-slate-500 sm:col-span-2">No discoverable people are nearby right now. Try again later or increase your discovery radius.</div>';
        } catch(error) {
            statusBox.textContent='We could not load nearby people. Please try again.';
        } finally {
            button.disabled=false;
            button.textContent='Refresh nearby people';
        }
    }, error => {
        statusBox.textContent=locationErrorMessage(error);
        button.disabled=false;
        button.textContent='Find people nearby';
    }, {enableHighAccuracy:true,maximumAge:60000,timeout:15000});
}

button.addEventListener('click', showLocationModal);
allowLocation.addEventListener('click', findPeople);
cancelLocation.addEventListener('click', hideLocationModal);

// On mobile, use a visible user-initiated button before calling the browser geolocation API.
// This makes the permission request predictable and avoids silently failing on page load.
</script>
@endsection
