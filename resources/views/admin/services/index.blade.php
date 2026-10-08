@extends('admin.layout')

@section('title', 'Services')
@section('page_title', 'Services')
@section('page_description', 'Kelola layanan BLA dalam tiga area layanan utama.')

@section('page_actions')
<a class="btn" href="{{ route('admin.services.create') }}">Add service</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-heading">
        <h2>Service catalog</h2>
        <span class="muted">{{ $services->count() }} services</span>
    </div>
    <div class="data-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Service area</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($services as $service)
                <tr>
                    <td>
                        <strong>{{ $service->name }}</strong>
                        <span class="subtext">{{ $service->slug }}</span>
                    </td>
                    <td>{{ __('site.services.areas.' . $service->service_area) }}</td>
                    <td><span class="badge">{{ $service->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-light" href="{{ route('admin.services.edit', $service) }}">Edit</a>
                            <form action="{{ route('admin.services.destroy', $service) }}" method="POST" onsubmit="return confirm('Delete this service?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="muted">No services have been added.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
