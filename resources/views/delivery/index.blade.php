@extends('layouts.public')

@section('title', __('site.delivery.title'))
@section('meta_description', __('site.delivery.intro'))

@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
<style>
    #delivery-map { min-height: 420px; }
    .leaflet-container { font: inherit; }
    @media (max-width: 640px) { #delivery-map { min-height: 340px; } }
</style>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="" defer></script>
@endpush

@section('content')
<section class="bg-gradient-to-br from-bla-50 via-white to-teal-50 py-12 md:py-16">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.delivery.eyebrow') }}</p>
        <h1 class="mt-3 font-display text-4xl font-extrabold text-bla-900 md:text-5xl">{{ __('site.delivery.title') }}</h1>
        <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ __('site.delivery.intro') }}</p>
    </div>
</section>

<section class="py-10 md:py-14">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        @if ($coverageAreas->isNotEmpty())
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 md:px-7">
                <div>
                    <h2 class="font-display text-xl font-extrabold text-bla-900">{{ __('site.delivery.coverage') }}</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ __('site.delivery.max_radius', ['radius' => $coverageAreas->max('radius_km')]) }}</p>
                </div>
                <span class="inline-flex items-center gap-2 rounded-full bg-bla-50 px-3 py-2 text-xs font-bold text-bla-700">
                    <i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $coverageAreas->count() }} {{ __('site.delivery.coverage') }}
                </span>
            </div>
            <div id="delivery-map" class="bg-slate-100" role="region" aria-label="{{ __('site.delivery.coverage') }}">
                <p class="p-6 text-sm text-slate-500">{{ __('site.delivery.map_loading') }}</p>
            </div>
        </div>
        @endif
    </div>
</section>

<section class="pb-14 md:pb-20">
    <div class="mx-auto w-[min(1400px,calc(100%-32px))]">
        <div class="mb-7">
            <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.delivery.eyebrow') }}</p>
            <h2 class="mt-2 font-display text-3xl font-extrabold text-bla-900 md:text-4xl">{{ __('site.delivery.vehicle_rates') }}</h2>
            <p class="mt-3 max-w-2xl leading-7 text-slate-600">{{ __('site.delivery.vehicle_rates_intro') }}</p>
        </div>

        @if ($vehicles->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-600">{{ __('site.delivery.empty') }}</div>
        @else
        <div class="grid gap-5 lg:grid-cols-3">
            @foreach ($vehicles as $vehicle)
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex min-h-44 items-center justify-center bg-gradient-to-br from-bla-50 to-slate-100 p-5">
                    @if ($vehicle->image_path)
                    <img src="{{ asset('storage/' . $vehicle->image_path) }}" alt="{{ $vehicle->name }}" class="h-36 w-full object-contain">
                    @else
                    <div class="grid h-28 w-28 place-items-center rounded-full bg-white text-5xl text-bla-600 shadow-sm" aria-hidden="true">
                        <i class="{{ $loop->first ? 'fa-solid fa-motorcycle' : ($loop->last ? 'fa-solid fa-truck' : 'fa-solid fa-truck-pickup') }}"></i>
                    </div>
                    @endif
                </div>
                <div class="p-5 md:p-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <h3 class="font-display text-2xl font-extrabold text-bla-900">{{ $vehicle->name }}</h3>
                        <span class="rounded-full bg-bla-50 px-3 py-1 text-xs font-bold text-bla-700">{{ __('site.delivery.weight') }}</span>
                    </div>
                    <p class="mt-2 min-h-10 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $vehicle->max_weight_label }}</p>
                    <div class="mt-5 overflow-x-auto rounded-xl border border-slate-200">
                        <table class="w-full min-w-[290px] text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-3 py-3">{{ __('site.delivery.distance') }}</th>
                                    <th class="px-3 py-3 text-right">{{ __('site.delivery.rate') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($vehicle->rates as $rate)
                                <tr>
                                    <td class="px-3 py-3 font-semibold text-slate-700">{{ number_format((float) $rate->distance_min_km, 0) }}–{{ number_format((float) $rate->distance_max_km, 0) }} km</td>
                                    <td class="px-3 py-3 text-right font-bold text-bla-800">
                                        @if ($rate->is_price_on_application)
                                        {{ __('site.delivery.price_on_application') }}
                                        @else
                                        Rp {{ number_format((float) $rate->fee, 0, ',', '.') }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <a class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-xl bg-bla-600 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-bla-700" href="https://wa.me/{{ preg_replace('/\D+/', '', config('bla.contact.whatsapp_number')) }}?text={{ rawurlencode(__('site.delivery.whatsapp_message', ['vehicle' => $vehicle->name])) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('site.delivery.ask_delivery') }}: {{ $vehicle->name }}">
                        <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>{{ __('site.delivery.ask_delivery') }}
                    </a>
                </div>
            </article>
            @endforeach
        </div>
        @endif
    </div>
</section>

@if ($coverageAreas->isNotEmpty())
@php
$mapAreas = $coverageAreas->map(function ($area) {
    return [
        'name' => $area->name,
        'latitude' => (float) $area->latitude,
        'longitude' => (float) $area->longitude,
        'radius' => (int) $area->radius_km,
    ];
})->values();
@endphp
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapElement = document.getElementById('delivery-map');
        if (!mapElement) return;

        var areas = @json($mapAreas);
        if (typeof L === 'undefined') {
            var fallbackMessage = @json(__('site.delivery.map_unavailable'));
            mapElement.replaceChildren(document.createTextNode(fallbackMessage));
            return;
        }

        mapElement.replaceChildren();
        var map = L.map(mapElement, { scrollWheelZoom: false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var bounds = [];
        areas.forEach(function (area) {
            var center = [area.latitude, area.longitude];
            bounds.push(center);
            var latitudeDelta = area.radius / 111;
            var longitudeDelta = area.radius / (111 * Math.max(Math.cos(area.latitude * Math.PI / 180), 0.01));
            bounds.push(
                [area.latitude - latitudeDelta, area.longitude - longitudeDelta],
                [area.latitude + latitudeDelta, area.longitude + longitudeDelta]
            );
            L.marker(center).addTo(map).bindPopup(area.name);
            for (var radius = 5; radius < area.radius; radius += 5) {
                L.circle(center, {
                    radius: radius * 1000,
                    color: '#0f766e',
                    weight: 1,
                    fillColor: '#14b8a6',
                    fillOpacity: 0.025
                }).addTo(map);
            }
            L.circle(center, {
                radius: area.radius * 1000,
                color: '#0f766e',
                weight: 2,
                fillColor: '#14b8a6',
                fillOpacity: 0.06
            }).addTo(map);
        });
        if (bounds.length === 1) {
            map.setView(bounds[0], 9);
        } else {
            map.fitBounds(bounds, { padding: [30, 30] });
        }
        window.setTimeout(function () { map.invalidateSize(); }, 100);
    });
</script>
@endif
@endsection
