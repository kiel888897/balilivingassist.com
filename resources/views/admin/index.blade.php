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