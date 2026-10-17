@extends('admin.layout')

@section('title', 'Service areas')
@section('page_title', 'Service areas')
@section('page_description', 'Kelola area layanan yang mengelompokkan service di halaman publik.')

@section('page_actions')
<a class="btn" href="{{ route('admin.service-areas.create') }}">Add service area</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-heading">
        <h2>Service area list</h2>
        <span class="muted">{{ $areas->count() }} areas</span>
    </div>
    <div class="data-wrap">
        <table class="data-table" data-searchable>
            <thead>
                <tr>
                    <th>Area</th>
                    <th>Order</th>
                    <th>Services</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($areas as $area)
                <tr>
                    <td>
                        <strong>{{ $area->name }}</strong>
                        @if ($area->name_id)<span class="subtext">ID: {{ $area->name_id }}</span>@endif
                    </td>
                    <td>{{ $area->sort_order }}</td>
                    <td>{{ $area->services_count }}</td>
                    <td><span class="badge">{{ $area->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-light btn-icon" href="{{ route('admin.service-areas.edit', $area) }}" aria-label="Edit service area: {{ $area->name }}" title="Edit service area">
                                <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            </a>
                            @if ($area->services_count === 0)
                            <form action="{{ route('admin.service-areas.destroy', $area) }}" method="POST" onsubmit="return confirm('Delete this service area?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-icon" type="submit" aria-label="Delete service area: {{ $area->name }}" title="Delete service area">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i>
                                </button>
                            </form>
                            @else
                            <span class="muted" title="Move or remove its services first">In use</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="muted">No service areas have been added.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
