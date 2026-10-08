@extends('admin.layout')

@section('title', 'Products')
@section('page_title', 'Products')
@section('page_description', 'Kelola produk, harga, dan ketersediaan penjualan atau rental.')

@section('page_actions')
@if (Auth::user()->hasPermissionTo('catalog.manage'))
<a class="btn" href="{{ route('admin.products.create') }}">Add product</a>
@endif
@endsection

@section('content')
<section class="panel">
    <div class="panel-heading">
        <h2>Product catalog</h2>
        <span class="muted">{{ $products->count() }} products</span>
    </div>
    <div class="data-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Sale price</th>
                    <th>Rental price</th>
                    <th>Availability</th>
                    <th>Status</th>
                    @if (Auth::user()->hasPermissionTo('catalog.manage'))
                    <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                <tr>
                    <td>
                        <strong>{{ $product->name }}</strong>
                        <span class="subtext">{{ $product->sku ?: 'No SKU' }}</span>
                    </td>
                    <td>{{ $product->category->name ?? 'Uncategorized' }}</td>
                    <td>{{ $product->for_sale ? 'Rp' . number_format($product->sale_price, 0, ',', '.') : '-' }}</td>
                    <td>{{ $product->for_rental ? 'Rp' . number_format($product->rental_price, 0, ',', '.') : '-' }}</td>
                    <td>
                        @if ($product->for_sale)<span class="badge">SALE</span>@endif
                        @if ($product->for_rental)<span class="badge">RENT</span>@endif
                    </td>
                    <td><span class="badge">{{ $product->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td>
                    @if (Auth::user()->hasPermissionTo('catalog.manage'))
                    <td>
                        <div class="actions">
                            <a class="btn btn-light" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ Auth::user()->hasPermissionTo('catalog.manage') ? 7 : 6 }}" class="muted">No products have been added.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection