@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-4xl">
    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="font-bold text-indigo-600">LAMII DISCOVER</p><h1 class="mt-2 text-4xl font-black">People around you</h1><p class="mt-2 text-slate-600">Your exact location is never shown to other people.</p></div>
        <div class="flex gap-2"><a href="{{ route('connections.index') }}" class="rounded-xl border bg-white px-5 py-3 font-bold">Connections</a><button id="refresh-location" class="rounded-xl bg-indigo-600 px-5 py-3 font-bold text-white">Find people nearby</button></div>
    </div>
    <div id="location-status" class="mb-6 rounded-2xl border bg-white p-4 text-sm text-slate-600">Lamii needs your permission to check who is nearby. Your location is stored temporarily and expires automatically after 15 minutes.</div>
    <div id="people" class="grid gap-4 sm:grid-cols-2"></div>
</div>

<script>
const statusBox = document.getElementById('location-status'); const peopleBox = document.getElementById('people'); const button = document.getElementById('refresh-location'); const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
function escapeHtml(value = '') { const div = document.createElement('div'); div.textContent = value; return div.innerHTML; }
async function wave(id, button) { button.disabled = true; try { const r = await fetch('/connections/' + id, {method:'POST', headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}}); const data = await r.json(); if (!r.ok) throw new Error(data.message); button.textContent='👋 Wave sent'; } catch(e) { button.disabled=false; button.textContent=e.message || 'Try again'; } }
async function findPeople() {
 if (!navigator.geolocation) { statusBox.textContent = 'Your browser does not support location services.'; return; }
 button.disabled=true; button.textContent='Checking location…'; statusBox.textContent='Requesting your location…';
 navigator.geolocation.getCurrentPosition(async position => { const payload={latitude:position.coords.latitude,longitude:position.coords.longitude,accuracy:position.coords.accuracy}; try {
 await fetch('{{ route('location.update') }}',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(payload)});
 const response=await fetch('{{ route('discover.nearby') }}?latitude='+encodeURIComponent(payload.latitude)+'&longitude='+encodeURIComponent(payload.longitude),{headers:{'Accept':'application/json'}}); const data=await response.json();
 statusBox.textContent=`Showing discoverable people within ${data.radius_km} km. Their exact locations are hidden.`;
 peopleBox.innerHTML=data.people.length?data.people.map(person=>`<article class="rounded-3xl border bg-white p-5 shadow-sm"><div class="flex items-start gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 text-xl font-black text-indigo-700">${person.avatar?`<img src="${escapeHtml(person.avatar)}" class="h-full w-full object-cover" alt="">`:escapeHtml(person.name.charAt(0).toUpperCase())}</div><div class="min-w-0 flex-1"><h2 class="font-bold text-slate-900">${escapeHtml(person.name)}</h2><p class="mt-1 font-semibold text-indigo-600">📍 ${escapeHtml(person.distance)}</p><p class="mt-2 text-sm text-slate-600">${escapeHtml(person.bio||'New to Lamii')}</p><button onclick="wave(${person.id},this)" class="mt-4 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">👋 Wave</button></div></div></article>`).join(''):'<div class="rounded-3xl border bg-white p-8 text-center text-slate-500 sm:col-span-2">No discoverable people are nearby right now. Try again later or increase your discovery radius.</div>';
 } catch(error){statusBox.textContent='We could not load nearby people. Please try again.';} finally{button.disabled=false;button.textContent='Refresh nearby people';}},error=>{statusBox.textContent=error.code===1?'Location permission was denied. Allow location access to discover people nearby.':'We could not determine your location. Please try again.';button.disabled=false;button.textContent='Find people nearby';},{enableHighAccuracy:false,maximumAge:60000,timeout:10000}); }
button.addEventListener('click',findPeople);
</script>
@endsection
