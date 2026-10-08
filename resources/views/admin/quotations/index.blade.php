@extends('admin.layout')

@section('title', 'Quotations')
@section('page_title', 'Quotations')
@section('page_description', 'Review new customer requests, edit quotes and track manual payment status.')

@section('content')
<section class="stats-grid" aria-label="Quotation summary">
    <div class="stat-card">
        <div class="stat-label">New requests</div>
        <div class="stat-value">{{ number_format($newCount) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Awaiting payment</div>
        <div class="stat-value">{{ number_format($quotations->where('status', 'payment_requested')->count()) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Orders</div>
        <div class="stat-value">{{ number_format($quotations->whereIn('status', \App\Models\Quotation::ORDER_STATUSES)->count()) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">All requests</div>
        <div class="stat-value">{{ number_format($quotations->count()) }}</div>
    </div>
</section>

<section class="panel">
    <div class="panel-heading">
        <h2>Customer quotation requests</h2>
        <span class="muted">{{ $quotations->count() }} total</span>
    </div>
    <div class="data-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Quotation</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotations as $quotation)
                <tr>
                    <td><strong>{{ $quotation->quote_number }}</strong></td>
                    <td>
                        <strong>{{ $quotation->customer_name }}</strong>
                        <span class="subtext">{{ $quotation->customer_phone }}</span>
                    </td>
                    <td>{{ $quotation->items_count }}</td>
                    <td>Rp {{ number_format($quotation->total, 0, ',', '.') }}</td>
                    <td><span class="badge">{{ strtoupper(str_replace('_', ' ', $quotation->status)) }}</span></td>
                    <td>{{ $quotation->created_at->format('d M Y H:i') }}</td>
                    <td><a class="btn btn-light" href="{{ route('admin.quotations.edit', $quotation) }}">Review / edit</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="muted">No customer quotation requests have been submitted.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
