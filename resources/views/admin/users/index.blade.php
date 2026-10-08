@extends('admin.layout')

@section('title', 'Users')
@section('page_title', 'User management')
@section('page_description', 'Kelola akun pengguna dan role akses BLA.')

@section('page_actions')
<a class="btn" href="{{ route('admin.users.create') }}">Add user</a>
@endsection

@section('content')
<section class="panel">
    <div class="panel-heading">
        <h2>Users</h2>
        <span class="muted">{{ $users->count() }} accounts</span>
    </div>
    <div class="data-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @forelse ($user->roles as $role)
                        <span class="badge">{{ $role->name }}</span>
                        @empty
                        <span class="muted">No role</span>
                        @endforelse
                    </td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="actions">
                            <a class="btn btn-light" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            @if ($user->id !== Auth::id())
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user account?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">Delete</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="muted">No user accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection