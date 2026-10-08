<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCoverageArea;
use App\Models\DeliveryRate;
use App\Models\DeliveryVehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminDeliveryController extends Controller
{
    public function index()
    {
        return view('admin.delivery.index', [
            'vehicles' => DeliveryVehicle::with('rates')->orderBy('sort_order')->orderBy('id')->get(),
            'coverageAreas' => DeliveryCoverageArea::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $data = $this->validateVehicle($request);
        unset($data['remove_image']);
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('delivery-vehicles', 'public');
        }

        DeliveryVehicle::create($data);

        return redirect()->route('admin.delivery')->with('status', 'Delivery vehicle created.');
    }

    public function updateVehicle(Request $request, DeliveryVehicle $vehicle)
    {
        $data = $this->validateVehicle($request, $vehicle);
        $removeImage = $request->boolean('remove_image');
        unset($data['remove_image']);

        if ($removeImage && $vehicle->image_path) {
            Storage::disk('public')->delete($vehicle->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($vehicle->image_path) {
                Storage::disk('public')->delete($vehicle->image_path);
            }
            $data['image_path'] = $request->file('image')->store('delivery-vehicles', 'public');
        }

        $vehicle->update($data);

        return redirect()->route('admin.delivery')->with('status', 'Delivery vehicle updated.');
    }

    public function destroyVehicle(DeliveryVehicle $vehicle)
    {
        $imagePath = $vehicle->image_path;
        $vehicle->delete();

        if ($imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()->route('admin.delivery')->with('status', 'Delivery vehicle deleted.');
    }

    public function storeRate(Request $request, DeliveryVehicle $vehicle)
    {
        $data = $this->validateRate($request, $vehicle);
        $vehicle->rates()->create($data);

        return redirect()->route('admin.delivery')->with('status', 'Delivery rate created.');
    }

    public function updateRate(Request $request, DeliveryVehicle $vehicle, DeliveryRate $rate)
    {
        abort_unless($rate->delivery_vehicle_id === $vehicle->id, 404);

        $rate->update($this->validateRate($request, $vehicle, $rate));

        return redirect()->route('admin.delivery')->with('status', 'Delivery rate updated.');
    }

    public function destroyRate(DeliveryVehicle $vehicle, DeliveryRate $rate)
    {
        abort_unless($rate->delivery_vehicle_id === $vehicle->id, 404);
        $rate->delete();

        return redirect()->route('admin.delivery')->with('status', 'Delivery rate deleted.');
    }

    public function storeCoverageArea(Request $request)
    {
        DeliveryCoverageArea::create($this->validateCoverageArea($request));

        return redirect()->route('admin.delivery')->with('status', 'Coverage origin created.');
    }

    public function updateCoverageArea(Request $request, DeliveryCoverageArea $area)
    {
        $area->update($this->validateCoverageArea($request));

        return redirect()->route('admin.delivery')->with('status', 'Coverage origin updated.');
    }

    public function destroyCoverageArea(DeliveryCoverageArea $area)
    {
        $area->delete();

        return redirect()->route('admin.delivery')->with('status', 'Coverage origin deleted.');
    }

    private function validateVehicle(Request $request, ?DeliveryVehicle $vehicle = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('delivery_vehicles', 'name')->ignore($vehicle ? $vehicle->id : null),
            ],
            'max_weight_label' => ['required', 'string', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
        ]);
    }

    private function validateRate(Request $request, DeliveryVehicle $vehicle, ?DeliveryRate $rate = null): array
    {
        $data = $request->validate([
            'distance_min_km' => ['required', 'numeric', 'min:0', 'max:1000'],
            'distance_max_km' => ['required', 'numeric', 'gt:distance_min_km', 'max:1000'],
            'fee' => ['nullable', 'required_if:is_price_on_application,0', 'numeric', 'min:0', 'max:9999999999.99'],
            'is_price_on_application' => ['required', 'boolean'],
        ]);

        $overlaps = $vehicle->rates()
            ->where('distance_min_km', '<', $data['distance_max_km'])
            ->where('distance_max_km', '>', $data['distance_min_km'])
            ->when($rate, function ($query) use ($rate) {
                $query->where('id', '!=', $rate->id);
            })
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages([
                'distance_min_km' => 'This distance range overlaps another rate for this vehicle.',
            ]);
        }

        $data['fee'] = $data['is_price_on_application'] ? null : $data['fee'];

        return $data;
    }

    private function validateCoverageArea(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['required', 'integer', 'min:1', 'max:500'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
