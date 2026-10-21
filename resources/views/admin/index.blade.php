@extends('admin.layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_description', 'Ringkasan katalog dan aktivitas inventaris BLA.')

@section('page_actions')
@if (Auth::user()->hasPermissionTo('catalog.manage'))
<a class="btn" href="{{ route('admin.products.create') }}">Add product</a>
@endif
@endsection

@section('content')
<section class="stats-grid" aria-label="Catalog statistics">
    <div class="stat-card">
        <div class="stat-label">Total products</div>
        <div class="stat-value">{{ number_format($stats['products']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Categories</div>
        <div class="stat-value">{{ number_format($stats['categories']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Available for sale</div>
        <div class="stat-value">{{ number_format($stats['sales']) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Available for rental</div>
        <div class="stat-value">{{ number_format($stats['rentals']) }}</div>
    </div>
</section>

@if ($workflowStats)
<section class="content-grid" aria-label="Quotation and order overview" style="margin-bottom:22px;">
    <article class="panel" style="overflow:hidden;border-top:3px solid #0e766e;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px 12px;">
            <div><span style="display:inline-flex;align-items:center;gap:8px;color:#0e766e;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> Sales workflow</span><h2 style="margin:9px 0 0;color:#173d46;font:800 20px 'Manrope',sans-serif;">Quotations</h2></div>
            <span style="display:grid;width:42px;height:42px;place-items:center;border-radius:12px;background:#e8f5f3;color:#0e766e;font-size:17px;"><i class="fa-solid fa-file-signature" aria-hidden="true"></i></span>
        </div>
        <div style="padding:0 22px 20px;"><div style="color:#173d46;font:800 34px 'Manrope',sans-serif;">{{ number_format($workflowStats['quotations']) }}</div><p class="subtext" style="margin:3px 0 16px;">All quotation requests and offers</p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:17px;"><span class="badge">{{ $workflowStats['waiting_admin'] }} waiting for admin</span><span class="badge">{{ $workflowStats['waiting_customer'] }} waiting for customer</span></div>
            <a class="btn btn-light" href="{{ route('admin.quotations') }}">Open quotations <i class="fa-solid fa-arrow-right ml-1" aria-hidden="true"></i></a>
        </div>
    </article>

    <article class="panel" style="overflow:hidden;border-top:3px solid #d39b38;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;padding:20px 22px 12px;">
            <div><span style="display:inline-flex;align-items:center;gap:8px;color:#9a6815;font-size:12px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;"><i class="fa-solid fa-box-open" aria-hidden="true"></i> Fulfilment</span><h2 style="margin:9px 0 0;color:#173d46;font:800 20px 'Manrope',sans-serif;">Orders</h2></div>
            <span style="display:grid;width:42px;height:42px;place-items:center;border-radius:12px;background:#fff5df;color:#a16c11;font-size:17px;"><i class="fa-solid fa-truck-fast" aria-hidden="true"></i></span>
        </div>
        <div style="padding:0 22px 20px;"><div style="color:#173d46;font:800 34px 'Manrope',sans-serif;">{{ number_format($workflowStats['orders']) }}</div><p class="subtext" style="margin:3px 0 16px;">Orders created from accepted quotations</p>
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:17px;"><span class="badge">{{ $workflowStats['awaiting_payment'] }} awaiting payment</span><span class="badge">{{ $workflowStats['payment_review'] }} proof review</span></div>
            <a class="btn btn-light" href="{{ route('admin.quotations', ['status' => 'orders']) }}">Open orders <i class="fa-solid fa-arrow-right ml-1" aria-hidden="true"></i></a>
        </div>
    </article>
</section>
@endif

@if (Auth::user()->hasPermissionTo('catalog.view'))
<div class="content-grid">
    <section class="panel">
        <div class="panel-heading">
            <h2>Categories</h2>
            @if (Auth::user()->hasPermissionTo('categories.manage'))
            <a class="topbar-link" href="{{ route('admin.categories') }}">Manage categories</a>
            @else
            <span class="muted">{{ $categories->count() }} total</span>
            @endif
        </div>
        <div class="data-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Products</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                    <tr>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td><span class="badge">{{ strtoupper($category->type) }}</span></td>
                        <td>{{ $category->products_count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="muted">No categories yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="panel-heading">
            <h2>Recent products</h2>
            @if (Auth::user()->hasPermissionTo('catalog.view'))
            <a class="topbar-link" href="{{ route('admin.products') }}">View catalog</a>
            @endif
        </div>
        <div class="data-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                    <tr>
                        <td><strong>{{ $product->name }}</strong><span class="subtext">{{ $product->sku ?: 'No SKU' }}</span></td>
                        <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                        <td>
                            @if ($product->for_sale)
                            Rp{{ number_format($product->sale_price, 0, ',', '.') }}
                            @else
                            Rp{{ number_format($product->rental_price, 0, ',', '.') }}
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="muted">No products yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endif
@endsection
