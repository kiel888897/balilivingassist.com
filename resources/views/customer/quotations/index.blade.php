@extends('layouts.public')

@section('title', __('site.quotation.title'))
@section('meta_description', __('site.auth.account_intro'))
@section('meta_robots', 'noindex,nofollow')

@section('content')
<section class="min-h-[60vh] py-10 md:py-14">
    <div class="mx-auto w-[min(1100px,calc(100%-32px))]">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.nav.account') }}</p>
                <h1 class="mt-2 font-display text-3xl font-extrabold text-bla-900">{{ __('site.quotation.title') }}</h1>
            </div>
            <a href="{{ route('customer.orders') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:border-bla-500 hover:text-bla-700">{{ __('site.quotation.orders') }}</a>
        </div>
        <div class="mt-7 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            @forelse ($quotations as $quotation)
            <article class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 p-5 last:border-b-0 md:px-6">
                <div>
                    <p class="font-bold text-bla-900">{{ $quotation->quote_number }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('site.quotation.created') }} · {{ $quotation->created_at->format('d M Y') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-bla-50 px-3 py-1.5 text-xs font-bold text-bla-700">{{ __('site.quotation.status_labels.' . $quotation->status) }}</span>
                    <strong class="text-slate-900">Rp {{ number_format($quotation->total, 0, ',', '.') }}</strong>
                    <a href="{{ route('customer.quotations.show', $quotation) }}" class="rounded-lg bg-bla-600 px-4 py-2 text-sm font-bold text-white hover:bg-bla-700">{{ __('site.quotation.view') }}</a>
                </div>
            </article>
            @empty
            <div class="p-8 text-center text-slate-600">{{ __('site.quotation.empty_quotes') }}</div>
            @endforelse
        </div>
    </div>
</section>
@endsection
