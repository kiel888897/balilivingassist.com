<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::with('roles')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(),
            'roles' => Role::orderBy('name')->get(),
            'selectedRole' => null,
            'formAction' => route('admin.users.store'),
            'formMethod' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $user->roles()->sync([$data['role_id']]);

        return redirect()->route('admin.users')->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        $user->load('roles');

        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'selectedRole' => $user->roles->first(),
            'formAction' => route('admin.users.update', $user),
            'formMethod' => 'PUT',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
        ]);

        $newRole = Role::findOrFail($data['role_id']);
        $isCurrentSuperAdmin = $user->roles()->where('slug', 'super_admin')->exists();

        if ($user->is(auth()->user()) && $isCurrentSuperAdmin && $newRole->slug !== 'super_admin') {
            return back()->with('error', 'You cannot remove your own Super Admin role.')->withInput();
        }

        if ($isCurrentSuperAdmin && $newRole->slug !== 'super_admin' && $this->isLastSuperAdmin($user)) {
            return back()->with('error', 'The last Super Admin cannot be demoted.')->withInput();
        }

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->roles()->sync([$newRole->id]);

        return redirect()->route('admin.users')->with('status', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return redirect()->route('admin.users')->with('error', 'You cannot delete your own account.');
        }

        $isSuperAdmin = $user->roles()->where('slug', 'super_admin')->exists();

        if ($isSuperAdmin && $this->isLastSuperAdmin($user)) {
            return redirect()->route('admin.users')->with('error', 'The last Super Admin cannot be deleted.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('status', 'User deleted.');
    }

    private function isLastSuperAdmin(User $user): bool
    {
        return User::whereHas('roles', function ($query) {
            $query->where('slug', 'super_admin');
        })->count() <= 1;
    }
}
