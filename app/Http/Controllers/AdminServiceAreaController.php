<?php

namespace App\Http\Controllers;

use App\Models\ServiceArea;
use App\Support\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminServiceAreaController extends Controller
{
    public function index()
    {
        return view('admin.service-areas.index', [
            'areas' => ServiceArea::withCount('services')->ordered()->get(),
        ]);
    }

    public function create()
    {
        return $this->form(new ServiceArea(['is_active' => true, 'sort_order' => (int) ServiceArea::max('sort_order') + 1]), route('admin.service-areas.store'), 'POST');
    }

    public function store(Request $request)
    {
        $data = $this->validateArea($request);
        $data['key'] = $this->uniqueKey($data['name']);
        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = ImageUploader::store($request->file('image'), 'service-areas') ?: null;
        }

        ServiceArea::create($data);

        return redirect()->route('admin.service-areas')->with('status', 'Service area created.');
    }

    public function edit(ServiceArea $serviceArea)
    {
        return $this->form($serviceArea, route('admin.service-areas.update', $serviceArea), 'PUT');
    }

    public function update(Request $request, ServiceArea $serviceArea)
    {
        $data = $this->validateArea($request);
        $oldImage = $serviceArea->image_path;
        unset($data['image'], $data['remove_image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = ImageUploader::store($request->file('image'), 'service-areas') ?: $oldImage;
        } elseif ($request->boolean('remove_image')) {
            $data['image_path'] = null;
        }

        $serviceArea->update($data);

        if ($oldImage && $serviceArea->image_path !== $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('admin.service-areas')->with('status', 'Service area updated.');
    }

    public function destroy(ServiceArea $serviceArea)
    {
        if ($serviceArea->services()->exists()) {
            return redirect()->route('admin.service-areas')
                ->with('error', 'This service area still has services. Move or delete them first.');
        }

        if ($serviceArea->image_path) {
            Storage::disk('public')->delete($serviceArea->image_path);
        }

        $serviceArea->delete();

        return redirect()->route('admin.service-areas')->with('status', 'Service area deleted.');
    }

    private function form(ServiceArea $area, string $formAction, string $formMethod)
    {
        return view('admin.service-areas.form', [
            'area' => $area,
            'formAction' => $formAction,
            'formMethod' => $formMethod,
        ]);
    }

    private function validateArea(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_id' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function uniqueKey(string $name): string
    {
        $base = Str::slug($name, '_') ?: 'area';
        $key = $base;
        $suffix = 2;

        while (ServiceArea::where('key', $key)->exists()) {
            $key = $base . '_' . $suffix++;
        }

        return $key;
    }
}
