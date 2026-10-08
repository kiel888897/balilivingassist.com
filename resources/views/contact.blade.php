@extends('layouts.public')

@section('title', __('site.nav.contact'))
@section('meta_description', __('site.contact.intro'))

@section('content')
<section class="bg-gradient-to-br from-bla-50 via-white to-teal-50 py-16 md:py-24">
    <div class="mx-auto grid w-[min(1100px,calc(100%-32px))] gap-10 md:grid-cols-2 md:items-center">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.contact.eyebrow') }}</p>
            <h1 class="mt-4 font-display text-4xl font-extrabold leading-tight text-bla-900 md:text-6xl">{{ __('site.contact.title') }}</h1>
            <p class="mt-5 text-lg leading-8 text-slate-600">{{ __('site.contact.intro') }}</p>
            <a href="{{ route('login') }}" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-bla-600 px-5 py-3 font-bold text-white hover:bg-bla-700">{{ __('site.home.assistance') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="grid gap-4">
            <div class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-bla-50 text-bla-700"><i class="fa-solid fa-location-dot" aria-hidden="true"></i></span>
                <div><h2 class="font-bold text-bla-900">{{ __('site.contact.hours') }}</h2><p class="mt-1 text-slate-600">{{ __('site.contact.area') }}</p></div>
            </div>
            <div class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-bla-50 text-bla-700"><i class="fa-solid fa-envelope" aria-hidden="true"></i></span>
                <div>
                    <h2 class="font-bold text-bla-900">{{ __('site.contact.email') }}</h2>
                    <a href="mailto:{{ config('bla.contact.email') }}" class="mt-1 text-slate-600 hover:text-bla-700">{{ config('bla.contact.email') }}</a>
                </div>
            </div>
            <div class="flex gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-bla-50 text-bla-700"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></span>
                <div>
                    <h2 class="font-bold text-bla-900">{{ __('site.contact.phone') }}</h2>
                    <a href="{{ config('bla.social.whatsapp') }}" target="_blank" rel="noopener noreferrer" class="mt-1 text-slate-600 hover:text-bla-700">{{ config('bla.contact.whatsapp_number') }}</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
