<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminServiceController extends Controller
{
    public function index()
    {
        return view('admin.services.index', [
            'services' => Service::orderBy('service_area')->orderBy('id')->get(),
        ]);
    }

    public function create()
    {
        return $this->form(new Service(), route('admin.services.store'), 'POST');
    }

    public function store(Request $request)
    {
        $data = $this->validateService($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        Service::create($data);

        return redirect()->route('admin.services')->with('status', 'Service created.');
    }

    public function edit(Service $service)
    {
        return $this->form($service, route('admin.services.update', $service), 'PUT');
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validateService($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $service);

        $service->update($data);

        return redirect()->route('admin.services')->with('status', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        if ($service->image_path) {
            Storage::disk('public')->delete($service->image_path);
        }

        $service->delete();

        return redirect()->route('admin.services')->with('status', 'Service deleted.');
    }

    private function form(Service $service, string $formAction, string $formMethod)
    {
        return view('admin.services.form', [
            'service' => $service,
            'serviceAreas' => config('bla.service_areas'),
            'formAction' => $formAction,
            'formMethod' => $formMethod,
        ]);
    }

    private function validateService(Request $request): array
    {
        $data = $request->validate([
            'service_area' => ['required', Rule::in(config('bla.service_areas'))],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);

        return $data;
    }

    private function uniqueSlug(string $name, ?Service $service = null): string
    {
        $baseSlug = Str::slug($name) ?: 'service';
        $slug = $baseSlug;
        $suffix = 2;

        while (Service::where('slug', $slug)
            ->when($service, function ($query) use ($service) {
                $query->where('id', '!=', $service->id);
            })
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
