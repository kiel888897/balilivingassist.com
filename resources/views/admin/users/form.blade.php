@extends('admin.layout')

@section('title', $user->exists ? 'Edit user' : 'Add user')
@section('page_title', $user->exists ? 'Edit user' : 'Add user')
@section('page_description', 'Atur data akun dan satu role akses untuk pengguna ini.')

@section('page_actions')
<a class="btn btn-light" href="{{ route('admin.users') }}">Back to users</a>
@endsection

@section('content')
<form class="panel form-panel" action="{{ $formAction }}" method="POST">
    @csrf
    @if ($formMethod !== 'POST')
    @method($formMethod)
    @endif

    <div class="form-grid">
        <div class="field field-wide">
            <label for="name">Full name</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" autocomplete="name">
            @error('name') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field field-wide">
            <label for="role_id">Role</label>
            <select id="role_id" name="role_id" required>
                <option value="">Select a role</option>
                @foreach ($roles as $role)
                <option value="{{ $role->id }}" {{ (string) old('role_id', optional($selectedRole)->id) === (string) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password">{{ $user->exists ? 'New password (optional)' : 'Password' }}</label>
            <input id="password" type="password" name="password" {{ $user->exists ? '' : 'required' }} minlength="12" autocomplete="new-password">
            @if ($user->exists)<span class="muted">Leave blank to keep the current password.</span>@endif
            @error('password') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" {{ $user->exists ? '' : 'required' }} minlength="12" autocomplete="new-password">
        </div>
    </div>

    <div class="form-actions">
        <a class="btn btn-light" href="{{ route('admin.users') }}">Cancel</a>
        <button class="btn" type="submit">{{ $user->exists ? 'Save changes' : 'Create user' }}</button>
    </div>
</form>
@endsection