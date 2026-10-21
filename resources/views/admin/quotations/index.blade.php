@extends('admin.layout')

@section('title', 'Quotations & Orders')
@section('page_title', $filter === 'orders' || in_array($filter, ['awaiting_payment', 'payment_review', 'paid', 'cancelled']) ? 'Orders' : 'Quotations')
@section('page_description', 'A clear workspace for customer requests, quotation decisions, and order follow-through.')

@section('page_actions')
<a class="btn" href="{{ route('admin.quotations.create') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i> Create quotation</a>
@endsection

@section('content')
@php
$isOrders = $filter === 'orders' || in_array($filter, ['awaiting_payment', 'payment_review', 'paid', 'cancelled']);
$tabs = $isOrders
    ? [['all orders', 'orders', $counts['orders']], ['awaiting payment', 'awaiting_payment', $counts['awaiting_payment']], ['payment proof review', 'payment_review', $counts['payment_review']], ['paid', 'paid', $counts['paid']], ['cancelled', 'cancelled', $counts['cancelled']]]
    : [['all quotations', 'all', null], ['waiting for admin', 'waiting_admin', $counts['waiting_admin']], ['draft', 'draft', $counts['draft']], ['waiting for customer', 'waiting_customer', $counts['waiting_customer']], ['rejected', 'rejected', $counts['rejected']]];
@endphp
<section class="stats-grid" aria-label="Workflow summary">
    <div class="stat-card"><div class="stat-label">Needs admin action</div><div class="stat-value">{{ number_format($counts['waiting_admin'] + $counts['payment_review']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Waiting for customer</div><div class="stat-value">{{ number_format($counts['waiting_customer']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Orders</div><div class="stat-value">{{ number_format($counts['orders']) }}</div></div>
    <div class="stat-card"><div class="stat-label">Payment review</div><div class="stat-value">{{ number_format($counts['payment_review']) }}</div></div>
</section>

<section class="panel" style="overflow:hidden;">
    <nav aria-label="Quotation and order filters" style="display:flex;gap:8px;overflow-x:auto;padding:14px 18px;border-bottom:1px solid #e5ecea;">
        @foreach ($tabs as [$label, $key, $count])
        <a href="{{ route('admin.quotations', ['status' => $key]) }}" style="display:inline-flex;align-items:center;gap:8px;white-space:nowrap;border-radius:999px;padding:9px 13px;text-decoration:none;font-size:12px;font-weight:800;{{ $filter === $key ? 'background:#0e6f65;color:#fff;' : 'background:#f3f7f6;color:#456168;' }}">
            {{ ucwords($label) }}@if (!is_null($count))<span style="display:inline-grid;min-width:20px;height:20px;place-items:center;border-radius:999px;padding:0 5px;background:{{ $filter === $key ? 'rgba(255,255,255,.2)' : '#fff' }};">{{ $count }}</span>@endif
        </a>
        @endforeach
    </nav>
    <div class="panel-heading"><div><h2>{{ $isOrders ? 'Order workspace' : 'Quotation workspace' }}</h2><span class="subtext">{{ $quotations->count() }} records · Most recent first</span></div></div>
    <div class="data-wrap">
        <table class="data-table" data-searchable>
            <thead><tr><th>Reference</th><th>Customer</th><th>Items</th><th>Total</th><th>Status</th><th>Updated</th><th>Action</th></tr></thead>
            <tbody>
                @forelse ($quotations as $quotation)
                <tr>
                    <td><strong>{{ $quotation->quote_number }}</strong><span class="subtext">{{ $quotation->is_order ? 'Order' : 'Quotation' }}</span></td>
                    <td><strong>{{ $quotation->customer_name }}</strong><span class="subtext">{{ $quotation->customer_phone }}</span></td>
                    <td>{{ $quotation->items_count }}</td>
                    <td><strong>Rp {{ number_format($quotation->total, 0, ',', '.') }}</strong></td>
                    <td><span class="badge">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span></td>
                    <td>{{ $quotation->updated_at->format('d M Y H:i') }}</td>
                    <td><a class="btn btn-light btn-icon" href="{{ route('admin.quotations.edit', $quotation) }}" aria-label="Open {{ $quotation->quote_number }}" title="Open record"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="muted" style="padding:32px;text-align:center;">No records in this queue yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
