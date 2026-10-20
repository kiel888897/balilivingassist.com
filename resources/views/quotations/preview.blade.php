@extends('layouts.public')

@php
$isInvoice = $documentType === 'invoice';
$documentTitle = $isInvoice ? 'Paid invoice' : 'Quotation';
$publicLink = $isInvoice ? route('quotations.preview.invoice', $quotation->public_token) : route('quotations.preview', $quotation->public_token);
@endphp

@section('title', $documentTitle . ' ' . $quotation->quote_number)
@section('meta_description', $documentTitle . ' ' . $quotation->quote_number)
@section('meta_robots', 'noindex,nofollow')

@push('head')
<style>
    .quotation-print-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 44px;
        padding: 10px 18px;
        border: 1px solid #0e6f65;
        border-radius: 8px;
        background-color: #0e6f65;
        color: #fff;
        font: 700 14px/1.25 "DM Sans", sans-serif;
        cursor: pointer;
        box-shadow: 0 2px 5px rgba(15, 23, 42, .18);
        transition: background-color .15s ease, box-shadow .15s ease;
    }
    .quotation-print-button:hover {
        background-color: #105951;
        border-color: #105951;
        color: #fff;
        box-shadow: 0 4px 9px rgba(15, 23, 42, .22);
    }
    .quotation-print-button:focus-visible {
        outline: 3px solid #99f6e4;
        outline-offset: 3px;
    }
    .quotation-grand-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-top: 22px;
        padding: 8px 12px;
        border: 1px solid #167985;
        background: #e9f1f2;
        color: #145866;
    }
    .quotation-terms-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        margin-top: 6px;
        border: 1px solid #d9e3e5;
        background: #f4f7f7;
    }
    .quotation-terms-grid > div { padding: 6px 9px; }
    .quotation-payment-box {
        width: min(100%, 370px);
        margin-top: 10px;
        border: 2px solid #167985;
        padding: 12px 14px;
        color: #145866;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.35;
    }
    @media (max-width: 640px) {
        .quotation-terms-grid { grid-template-columns: 1fr; }
    }
    @media print {
        @page { size: A4; margin: 14mm; }
        body { background: #fff !important; color: #111827 !important; }
        body > header, body > footer, .no-print { display: none !important; }
        #main-content { margin: 0 !important; padding: 0 !important; }
        .print-document { width: 100% !important; max-width: none !important; padding: 0 !important; margin: 0 !important; }
        .print-card { border: 0 !important; box-shadow: none !important; padding: 0 !important; }
        .print-brand { display: flex !important; }
        a { color: inherit !important; text-decoration: none !important; }
        .print-avoid-break { break-inside: avoid; }
    }
    .print-brand { display: none; }
</style>
@endpush

@section('content')
<main class="print-document mx-auto w-[min(900px,calc(100%-32px))] py-8 md:py-12">
    <div class="no-print mb-5 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('home') }}" class="font-bold text-bla-700">{{ __('site.brand') }}</a>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="quotation-print-button" onclick="window.print()">Print / Save PDF</button>
            @if (!$isInvoice && $quotation->hasPaidInvoice())
            <a class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800" href="{{ route('quotations.preview.invoice', $quotation->public_token) }}">View paid invoice</a>
            @endif
            @if (!$isInvoice && $quotation->status === 'payment_requested')
            <a class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800" href="{{ route('login') }}">Sign in to upload payment proof</a>
            @endif
        </div>
    </div>

    <article class="print-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-10">
        <header class="print-brand mb-8 items-center justify-between border-b border-slate-200 pb-5">
            <img src="{{ asset('images/bla-logos.png') }}" alt="{{ __('site.brand') }}" style="height:62px;width:auto;">
            <div class="text-right text-sm text-slate-600">
                <strong class="block text-lg text-slate-900">{{ $documentTitle }}</strong>
                <span>{{ $quotation->quote_number }}</span>
            </div>
        </header>
        <div class="flex flex-wrap items-start justify-between gap-5 border-b border-slate-200 pb-6">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-700">{{ $documentTitle }}</p>
                <h1 class="mt-2 font-display text-3xl font-extrabold text-slate-900">{{ $quotation->quote_number }}</h1>
            </div>
            <div class="text-right">
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-700">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span>
                @if ($isInvoice)
                <p class="mt-2 text-sm font-semibold text-green-700">Payment confirmed{{ $quotation->payment_confirmed_at ? ' · ' . $quotation->payment_confirmed_at->format('d M Y') : '' }}</p>
                @endif
                <p class="mt-2 text-sm text-slate-500">{{ $quotation->created_at->format('d M Y') }}</p>
            </div>
        </div>

        <section class="print-avoid-break grid gap-5 border-b border-slate-200 py-6 sm:grid-cols-2">
            <div>
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Customer</h2>
                <p class="mt-2 font-bold text-slate-900">{{ $quotation->customer_name }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ $quotation->customer_phone }}</p>
            </div>
            <div>
                <h2 class="text-xs font-extrabold uppercase tracking-wider text-slate-500">Delivery address</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $quotation->shipping_address }}</p>
            </div>
        </section>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm">
                <thead><tr class="border-b border-slate-300 text-xs uppercase tracking-wider text-slate-500"><th class="py-3 pr-3">Item</th><th class="py-3 px-2 text-right">Qty</th><th class="py-3 px-2 text-right">Unit price</th><th class="py-3 pl-2 text-right">Amount</th></tr></thead>
                <tbody>
                    @foreach ($quotation->items as $item)
                    <tr class="print-avoid-break border-b border-slate-100">
                        <td class="py-4 pr-3"><strong class="text-slate-900">{{ $item->description }}</strong>@if ($item->sku)<span class="mt-1 block text-xs text-slate-500">SKU: {{ $item->sku }}</span>@endif
                            @if ($item->options_snapshot)<span class="mt-1 block text-xs text-slate-500">{{ collect($item->options_snapshot)->map(function ($option) { return ($option['attribute'] ?? '') . ': ' . ($option['value'] ?? ''); })->implode(' · ') }}</span>@endif
                        </td>
                        <td class="py-4 px-2 text-right">{{ $item->quantity }}</td>
                        <td class="py-4 px-2 text-right whitespace-nowrap">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="py-4 pl-2 text-right whitespace-nowrap">Rp {{ number_format($item->line_total, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="ml-auto mt-5 grid max-w-sm gap-2 text-sm">
            <p class="flex justify-between"><span>Subtotal</span><strong>Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</strong></p>
            <p class="flex justify-between"><span>Delivery</span><strong>Rp {{ number_format($quotation->delivery_fee, 0, ',', '.') }}</strong></p>
            <p class="flex justify-between"><span>Discount</span><strong>− Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</strong></p>
        </div>

        <section class="print-avoid-break mt-5">
            <div class="quotation-grand-total">
                <div><strong class="block text-sm uppercase tracking-wide">GRAND TOTAL</strong><span class="block text-xs italic">VAT excluded</span></div>
                <strong class="text-xl">Rp {{ number_format($quotation->total, 0, ',', '.') }}</strong>
            </div>
        </section>

        <section class="print-avoid-break mt-4">
            <h2 class="font-display text-xs font-extrabold uppercase tracking-wider text-bla-700">Terms &amp; Conditions</h2>
            <div class="quotation-terms-grid text-xs leading-5 text-slate-700">
                <div>
                    <ol class="list-decimal space-y-0 pl-5">
                        <li>Price excludes 11% VAT.</li>
                        <li>Prices are valid for the Java and Bali regions only.</li>
                    </ol>
                </div>
                <div class="border-l border-slate-200">
                    <h3 class="text-[10px] font-extrabold uppercase tracking-wide text-slate-500">Terms of Payment</h3>
                    <p class="mt-1 font-bold text-slate-800">Full Payment in Advance</p>
                </div>
            </div>
        </section>

        <section class="print-avoid-break mt-2 text-xs italic leading-5 text-slate-500">
            <p>Prices stated above are subject to change without prior notice.</p>
        </section>

        @if (!$isInvoice && $quotation->payment_instructions)
        <section class="print-avoid-break quotation-payment-box">
            @foreach (preg_split('/\\r\\n|\\r|\\n/', $quotation->payment_instructions) as $instructionLine)
            <p>{{ $instructionLine }}</p>
            @endforeach
        </section>
        @endif

        <section class="print-avoid-break mt-4 text-sm leading-6 text-slate-700">
            <p>Best regards,</p>
            <p class="mt-12 font-extrabold text-slate-900">Bali Living Assist</p>
            <p class="text-xs">+62 851-7332-3293</p>
        </section>
        @if ($quotation->customer_note)
        <section class="print-avoid-break mt-6 border-t border-slate-200 pt-5">
            <h2 class="font-bold text-slate-900">Customer note</h2>
            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $quotation->customer_note }}</p>
        </section>
        @endif
        <footer class="mt-9 border-t border-slate-200 pt-4 text-center text-xs text-slate-500">{{ config('app.name') }} · {{ config('app.url') }}</footer>
    </article>
</main>
@endsection
