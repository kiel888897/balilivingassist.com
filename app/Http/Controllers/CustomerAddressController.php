<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAddressController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureCustomer();

        $addresses = $request->user()->addresses()
            ->reorder()
            ->orderByRaw('last_used_at IS NULL')
            ->orderByDesc('last_used_at')
            ->orderByDesc('id')
            ->get();

        return view('customer.addresses.index', compact('addresses'));
    }

    public function store(Request $request)
    {
        $this->ensureCustomer();
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $address = $request->user()->addresses()->create($data);

        return redirect()->route('customer.addresses')->with('status', __('site.addresses.created'));
    }

    public function update(Request $request, CustomerAddress $address)
    {
        $this->ensureCustomer();
        $this->ensureOwner($request, $address);
        $data = $request->validate([
            'label' => ['nullable', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:2000'],
        ]);

        $address->update($data);

        return redirect()->route('customer.addresses')->with('status', __('site.addresses.updated'));
    }

    public function destroy(Request $request, CustomerAddress $address)
    {
        $this->ensureCustomer();
        $this->ensureOwner($request, $address);
        $address->delete();

        return redirect()->route('customer.addresses')->with('status', __('site.addresses.deleted'));
    }

    private function ensureOwner(Request $request, CustomerAddress $address): void
    {
        abort_unless((int) $address->user_id === (int) $request->user()->id, 404);
    }

    private function ensureCustomer(): void
    {
        abort_unless(Auth::check() && Auth::user()->hasRole('customer') && !Auth::user()->hasPermissionTo('admin.access'), 403);
    }
}
