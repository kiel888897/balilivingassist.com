@extends('layouts.public')

@section('title', __('site.auth.account_title'))
@section('meta_description', __('site.auth.account_intro'))
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="mx-auto min-h-[60vh] w-[min(1000px,calc(100%-32px))] py-12 md:py-16">
    <div class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm md:p-10">
        <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.account') }}</p>
        <h1 class="mt-3 font-display text-3xl font-extrabold text-bla-900">{{ __('site.auth.account_title') }}</h1>
        <p class="mt-2 text-slate-600">{{ __('site.auth.account_intro') }}</p>
        <div class="mt-7 grid gap-4 sm:grid-cols-2">
            <a href="{{ route('customer.quotations') }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-bla-400 hover:shadow-sm">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-bla-50 text-lg text-bla-700"><i class="fa-solid fa-file-invoice" aria-hidden="true"></i></span>
                <span class="mt-4 block font-display text-lg font-extrabold text-bla-900">{{ __('site.quotation.title') }}</span>
                <span class="mt-1 block text-sm text-slate-500">{{ $quotationCount }} {{ __('site.quotation.title') }}</span>
            </a>
            <a href="{{ route('customer.orders') }}" class="rounded-2xl border border-slate-200 bg-white p-5 transition hover:border-bla-400 hover:shadow-sm">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-bla-50 text-lg text-bla-700"><i class="fa-solid fa-box" aria-hidden="true"></i></span>
                <span class="mt-4 block font-display text-lg font-extrabold text-bla-900">{{ __('site.quotation.orders') }}</span>
                <span class="mt-1 block text-sm text-slate-500">{{ $orderCount }} {{ __('site.quotation.orders') }}</span>
            </a>
        </div>
        <div class="mt-6 rounded-2xl bg-slate-50 p-5">
            <p class="font-bold text-bla-900">{{ $user->name }}</p>
            <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
            @if ($user->phone)
            <p class="mt-1 text-sm text-slate-500">{{ $user->phone }}</p>
            @endif
        </div>
        <form action="{{ route('logout') }}" method="POST" class="mt-6">
            @csrf
            <button type="submit" class="rounded-xl border border-slate-200 px-5 py-3 font-bold text-slate-700 hover:bg-slate-50">{{ app()->getLocale() === 'id' ? 'Keluar' : 'Sign out' }}</button>
        </form>
    </div>
</section>
@endsection
