@extends('admin.layout')

@section('title', $categoryLabel . ' categories')
@section('page_title', $categoryLabel . ' category management')
@section('page_description', 'Kelola kategori ' . $categoryLabel . ' secara terpisah dari katalog lainnya.')

@section('page_actions')
<a class="btn" href="{{ route($categoryRoute . '.create') }}">Add {{ strtolower($categoryLabel) }} category</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-heading">
        <h2>{{ $categoryLabel }} categories</h2>
        <span class="muted">{{ $categories->count() }} total</span>
    </div>
    <div class="data-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @if ($category->image_path)
                            <img src="{{ asset('storage/' . $category->image_path) }}" alt="" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px;">
                            @endif
                            <div><strong>{{ $category->name }}</strong><span class="subtext">{{ $category->slug }}</span></div>
                        </div>
                    </td>
                    <td>{{ $category->description ?: '-' }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td><span class="badge">{{ $category->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-light" href="{{ route($categoryRoute . '.edit', $category) }}">Edit</a>
                            @if ($category->products_count === 0)
                            <form action="{{ route($categoryRoute . '.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                            @else
                            <span class="muted" title="Move or remove its items first">In use</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="muted">No {{ strtolower($categoryLabel) }} categories have been added.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
