@extends('layouts.public')

@section('title', $quotation->quote_number)
@section('meta_description', __('site.quotation.title') . ' ' . $quotation->quote_number)
@section('meta_robots', 'noindex,nofollow')

@section('content')
@php
$whatsappMessage = __('site.quotation.whatsapp_customer_message', ['number' => $quotation->quote_number]);
$whatsappUrl = 'https://wa.me/' . preg_replace('/\D+/', '', config('bla.contact.whatsapp_number')) . '?text=' . rawurlencode($whatsappMessage);
@endphp
<section class="min-h-[60vh] py-10 md:py-14">
    <div class="mx-auto w-[min(1000px,calc(100%-32px))]">
        @if (session('status'))
        <p class="mb-5 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800" role="status">{{ session('status') }}</p>
        @endif
        <a href="{{ $quotation->isOrder() ? route('customer.orders') : route('customer.quotations') }}" class="text-sm font-bold text-bla-700 hover:text-bla-900">← {{ $quotation->isOrder() ? __('site.quotation.orders') : __('site.quotation.title') }}</a>
        <div class="no-print mt-5 flex flex-wrap gap-2">
            <a class="rounded-lg bg-bla-700 px-4 py-2 text-sm font-bold text-white" href="{{ route('quotations.preview', $quotation->public_token) }}" target="_blank" rel="noopener">Preview / share quotation</a>
            @if ($quotation->hasPaidInvoice())
            <a class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-800" href="{{ route('quotations.preview.invoice', $quotation->public_token) }}" target="_blank" rel="noopener">View paid invoice</a>
            @endif
        </div>
        <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-extrabold uppercase tracking-[.16em] text-bla-600">{{ __('site.quotation.number') }}</p>
                <h1 class="mt-2 font-display text-3xl font-extrabold text-bla-900">{{ $quotation->quote_number }}</h1>
            </div>
            <span class="rounded-full bg-bla-50 px-4 py-2 text-sm font-bold text-bla-700">{{ __('site.quotation.status_labels.' . $quotation->status) }}</span>
        </div>

        @if (!$quotation->isOrder() && $quotation->status === 'waiting_customer')
        <section class="mt-6 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-5 shadow-sm md:p-6">
            <div class="flex items-start gap-4">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-amber-100 text-amber-800"><i class="fa-solid fa-bell" aria-hidden="true"></i></span>
                <div><h2 class="font-display text-lg font-extrabold text-slate-900">Your decision is needed</h2><p class="mt-1 text-sm leading-6 text-slate-600">Review the items and total below. Accept to let the admin prepare an order, reject the quotation, or request a change.</p></div>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                <form action="{{ route('customer.quotations.respond', $quotation) }}" method="POST">@csrf<input type="hidden" name="action" value="accept"><button class="w-full rounded-xl bg-bla-700 px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-bla-800" type="submit"><i class="fa-solid fa-check mr-2" aria-hidden="true"></i>Accept quotation</button></form>
                <form action="{{ route('customer.quotations.respond', $quotation) }}" method="POST" class="space-y-2">@csrf<input type="hidden" name="action" value="request_changes"><input name="note" required maxlength="2000" placeholder="What should be changed?" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"><button class="w-full rounded-xl border border-bla-200 bg-white px-4 py-3 text-sm font-bold text-bla-800 hover:bg-bla-50" type="submit">Request changes</button></form>
                <form action="{{ route('customer.quotations.respond', $quotation) }}" method="POST" class="space-y-2">@csrf<input type="hidden" name="action" value="reject"><input name="note" required maxlength="2000" placeholder="Reason for rejecting" class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm"><button class="w-full rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-bold text-rose-800 hover:bg-rose-100" type="submit">Reject quotation</button></form>
            </div>
        </section>
        @elseif ($quotation->decision_note)
        <p class="mt-5 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700"><strong>Latest note:</strong> {{ $quotation->decision_note }}</p>
        @endif

        <div class="mt-7 grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
            <div class="space-y-6">
                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                    <h2 class="border-b border-slate-100 px-5 py-4 font-display text-lg font-extrabold text-bla-900">{{ __('site.quotation.items') }}</h2>
                    <div class="divide-y divide-slate-100">
                        @foreach ($quotation->items as $item)
                        <div class="flex flex-wrap items-start justify-between gap-4 px-5 py-4">
                            <div>
                                <p class="font-bold text-slate-900">{{ $item->description }}</p>
                                @if ($item->sku)<p class="mt-1 text-xs text-slate-500">{{ __('site.product.sku') }}: {{ $item->sku }}</p>@endif
                                @if ($item->options_snapshot)
                                @php
                                $itemOptions = collect($item->options_snapshot)->map(function ($option) {
                                    if (($option['attribute'] ?? null) === 'Rental period') {
                                        return __('site.rental.rental_period') . ': ' . __('site.rental.periods.' . ($option['value'] ?? 'daily'));
                                    }
                                    return ($option['attribute'] ?? '') . ': ' . ($option['value'] ?? '');
                                });
                                @endphp
                                <p class="mt-1 text-xs text-slate-500">{{ $itemOptions->implode(' · ') }}</p>
                                @endif
                                <p class="mt-1 text-sm text-slate-500">{{ $item->quantity }} × Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                            </div>
                            <strong class="text-slate-900">Rp {{ number_format($item->line_total, 0, ',', '.') }}</strong>
                        </div>
                        @endforeach
                    </div>
                    <div class="grid gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4 text-sm">
                        <p class="flex justify-between"><span>{{ __('site.quotation.subtotal') }}</span><strong>Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</strong></p>
                        <p class="flex justify-between"><span>{{ __('site.quotation.delivery_fee') }}</span><strong>Rp {{ number_format($quotation->delivery_fee, 0, ',', '.') }}</strong></p>
                        <p class="flex justify-between"><span>{{ __('site.quotation.discount') }}</span><strong>- Rp {{ number_format($quotation->discount_amount, 0, ',', '.') }}</strong></p>
                        <p class="mt-2 flex justify-between border-t border-slate-200 pt-3 text-base"><span class="font-bold">{{ __('site.quotation.total') }}</span><strong class="font-display text-lg text-bla-900">Rp {{ number_format($quotation->total, 0, ',', '.') }}</strong></p>
                    </div>
                </section>

                @if ($quotation->admin_note || (in_array($quotation->status, ['payment_requested', 'payment_review']) && $quotation->payment_instructions))
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    @if ($quotation->admin_note)
                    <h2 class="font-display font-extrabold text-bla-900">{{ __('site.quotation.admin_note') }}</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $quotation->admin_note }}</p>
                    @endif
                    @if (in_array($quotation->status, ['payment_requested', 'payment_review']) && $quotation->payment_instructions)
                    <h2 class="mt-5 font-display font-extrabold text-bla-900">{{ __('site.quotation.payment_instructions') }}</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $quotation->payment_instructions }}</p>
                    @endif
                </section>
                @endif
                @if ($quotation->status === 'payment_requested')
                <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
                    <h2 class="font-display font-extrabold text-amber-950">Upload payment proof</h2>
                    <p class="mt-2 text-sm leading-6 text-amber-900">Upload a JPG, PNG, WEBP, or PDF file (maximum 10 MB). The invoice becomes available after payment is confirmed by the admin.</p>
                    @if ($quotation->payment_rejection_reason)<p class="mt-3 rounded-lg border border-rose-200 bg-white p-3 text-sm text-rose-800"><strong>Previous proof was rejected:</strong> {{ $quotation->payment_rejection_reason }}</p>@endif
                    <form class="mt-4 space-y-3" action="{{ route('customer.quotations.payment-proofs.store', $quotation) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="proof" accept=".jpg,.jpeg,.png,.webp,.pdf" required class="block w-full rounded-lg border border-amber-300 bg-white p-2 text-sm">
                        @error('proof')<p class="text-sm font-semibold text-red-700">{{ $message }}</p>@enderror
                        <button class="rounded-lg bg-amber-800 px-4 py-2 text-sm font-bold text-white" type="submit">Upload proof</button>
                    </form>
                    @if ($quotation->paymentProofs->isNotEmpty())
                    <div class="mt-4 border-t border-amber-200 pt-3">
                        <p class="text-sm font-bold text-amber-950">Uploaded proofs</p>
                        @foreach ($quotation->paymentProofs as $proof)
                        <a class="mt-2 block text-sm font-semibold text-bla-700 underline" href="{{ route('quotation-payment-proofs.download', $proof) }}">{{ $proof->original_name }} · {{ $proof->created_at->format('d M Y H:i') }}</a>
                        @endforeach
                    </div>
                    @endif
                </section>
                @endif

                @if ($quotation->paymentProofs->isNotEmpty() && $quotation->status !== 'payment_requested')
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-display font-extrabold text-bla-900">Payment proofs</h2>
                    @foreach ($quotation->paymentProofs as $proof)
                    <a class="mt-2 block text-sm font-semibold text-bla-700 underline" href="{{ route('quotation-payment-proofs.download', $proof) }}">{{ $proof->original_name }} · {{ $proof->created_at->format('d M Y H:i') }}</a>
                    @endforeach
                </section>
                @endif
            </div>

            <aside class="space-y-4">
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="font-display font-extrabold text-bla-900">{{ __('site.quotation.shipping_address') }}</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $quotation->shipping_address }}</p>
                    @if ($quotation->customer_note)
                    <h3 class="mt-5 font-bold text-bla-900">{{ __('site.quotation.customer_note') }}</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $quotation->customer_note }}</p>
                    @endif
                </section>
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-bla-600 px-5 py-3 text-center font-bold text-white hover:bg-bla-700">
                    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>{{ __('site.quotation.contact_admin') }}
                </a>
            </aside>
        </div>
    </div>
</section>
@endsection
