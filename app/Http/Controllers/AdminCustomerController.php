<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCustomerController extends Controller
{
    public function index()
    {
        $customers = $this->customers()
            ->withCount('addresses')
            ->orderBy('name')
            ->get();

        return view('admin.contacts.index', ['customers' => $customers]);
    }

    public function create()
    {
        return view('admin.contacts.form', [
            'customer' => new User(),
            'formAction' => route('admin.contacts.store'),
            'formMethod' => 'POST',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->validationRules());

        DB::transaction(function () use ($data) {
            $customer = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                // The customer sets a password through the password-reset flow.
                'password' => Hash::make(Str::random(48)),
            ]);
            $customer->roles()->sync([Role::where('slug', 'customer')->firstOrFail()->id]);
            $this->saveAddresses($customer, $data['addresses'] ?? []);
        });

        return redirect()->route('admin.contacts')->with('status', 'Customer contact created. The customer can set a password using Forgot password.');
    }

    public function edit(User $user)
    {
        $customer = $this->customers()->with('addresses')->findOrFail($user->id);

        return view('admin.contacts.form', [
            'customer' => $customer,
            'formAction' => route('admin.contacts.update', $customer),
            'formMethod' => 'PUT',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $customer = $this->customers()->findOrFail($user->id);
        $data = $request->validate($this->validationRules($customer));

        DB::transaction(function () use ($customer, $data) {
            $customer->name = $data['name'];
            $customer->phone = $data['phone'] ?? null;
            $customer->email = $data['email'];
            $customer->save();

            $this->saveAddresses($customer, $data['addresses'] ?? []);
        });

        return redirect()->route('admin.contacts')->with('status', 'Customer contact updated.');
    }

    public function destroy(User $user)
    {
        $customer = $this->customers()->findOrFail($user->id);
        $customer->delete();

        return redirect()->route('admin.contacts')->with('status', 'Customer contact deleted.');
    }

    private function customers()
    {
        return User::whereHas('roles', function ($query) {
            $query->where('slug', 'customer');
        });
    }

    private function validationRules(?User $customer = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($customer),
            ],
            'addresses' => ['nullable', 'array'],
            'addresses.*.id' => ['nullable', 'integer'],
            'addresses.*.label' => ['nullable', 'string', 'max:100'],
            'addresses.*.address' => ['required', 'string', 'max:2000'],
        ];
    }

    private function saveAddresses(User $customer, array $addresses): void
    {
        $addressIds = collect($addresses)
            ->pluck('id')
            ->filter()
            ->map(function ($id) use ($customer) {
                abort_unless($customer->addresses()->whereKey($id)->exists(), 404);

                return (int) $id;
            })
            ->all();

        $customer->addresses()->whereNotIn('id', $addressIds)->delete();

        foreach ($addresses as $address) {
            $attributes = [
                'label' => $address['label'] ?? null,
                'address' => $address['address'],
            ];

            if (!empty($address['id'])) {
                $customer->addresses()->whereKey($address['id'])->update($attributes);
            } else {
                $customer->addresses()->create($attributes);
            }
        }
    }
}
