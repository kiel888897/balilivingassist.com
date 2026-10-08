@extends('admin.layout')

@section('title', 'Portfolio')
@section('page_title', 'Portfolio')
@section('page_description', 'Kelola project portofolio dan foto slider yang tampil di website.')

@section('page_actions')
<a class="btn" href="{{ route('admin.portfolio.create') }}">Add portfolio project</a>
@endsection

@section('content')
@if (session('status'))
<div class="status-message" role="status">{{ session('status') }}</div>
@endif

<section class="panel">
    <div class="panel-heading">
        <h2>Portfolio projects</h2>
        <span class="muted">{{ $projects->count() }} projects</span>
    </div>
    <div class="data-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Photos</th>
                    <th>Visibility</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                <tr>
                    <td>
                        <strong>{{ $project->title }}</strong>
                        <span class="subtext">{{ $project->slug }}</span>
                        @if ($project->is_sample)
                        <span class="badge">ILLUSTRATIVE SAMPLE</span>
                        @endif
                    </td>
                    <td>{{ $project->images_count }}</td>
                    <td><span class="badge">{{ $project->is_active ? 'ACTIVE' : 'HIDDEN' }}</span></td>
                    <td>{{ $project->sort_order }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-light" href="{{ route('admin.portfolio.edit', $project) }}">Edit</a>
                            <form action="{{ route('admin.portfolio.destroy', $project) }}" method="POST" onsubmit="return confirm('Delete this portfolio project and its photos?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="muted">No portfolio projects have been added.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
