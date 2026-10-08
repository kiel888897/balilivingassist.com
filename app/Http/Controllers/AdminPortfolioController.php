<?php

namespace App\Http\Controllers;

use App\Models\PortfolioImage;
use App\Models\PortfolioProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdminPortfolioController extends Controller
{
    public function index()
    {
        return view('admin.portfolio.index', [
            'projects' => PortfolioProject::withCount('images')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function create()
    {
        return $this->form(new PortfolioProject(), route('admin.portfolio.store'), 'POST');
    }

    public function store(Request $request)
    {
        $data = $this->validateProject($request, true);
        $uploads = $request->file('images', []);
        $data['slug'] = $this->uniqueSlug($data['title']);
        unset($data['images']);
        $uploadedPaths = [];

        try {
            $project = DB::transaction(function () use ($data, $uploads, &$uploadedPaths) {
                $project = PortfolioProject::create($data);
                $this->storeImages($project, $uploads, $uploadedPaths, 0);

                return $project;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);
            throw $exception;
        }

        return redirect()->route('admin.portfolio.edit', $project)->with('status', 'Portfolio project created.');
    }

    public function edit(PortfolioProject $project)
    {
        $project->load('images');

        return $this->form($project, route('admin.portfolio.update', $project), 'PUT');
    }

    public function update(Request $request, PortfolioProject $project)
    {
        $data = $this->validateProject($request, false);
        $galleryRows = $data['gallery'] ?? [];
        $deleteIds = array_map('intval', $data['delete_images'] ?? []);
        $uploads = $request->file('images', []);
        $existingImages = $project->images()->get()->keyBy('id');

        foreach (array_merge(array_keys($galleryRows), $deleteIds) as $imageId) {
            if (!$existingImages->has((int) $imageId)) {
                throw ValidationException::withMessages([
                    'gallery' => 'The selected image does not belong to this portfolio project.',
                ]);
            }
        }

        if ($existingImages->count() - count(array_unique($deleteIds)) + count($uploads) < 1) {
            throw ValidationException::withMessages([
                'images' => 'Each active portfolio project must have at least one photo.',
            ]);
        }

        unset($data['images'], $data['gallery'], $data['delete_images']);
        $data['slug'] = $this->uniqueSlug($data['title'], $project);
        $uploadedPaths = [];
        $deletedPaths = [];

        try {
            DB::transaction(function () use (
                $project,
                $data,
                $galleryRows,
                $deleteIds,
                $uploads,
                &$uploadedPaths,
                &$deletedPaths
            ) {
                $project->update($data);

                foreach ($galleryRows as $imageId => $row) {
                    if (in_array((int) $imageId, $deleteIds, true)) {
                        continue;
                    }

                    $project->images()
                        ->whereKey($imageId)
                        ->update([
                            'sort_order' => $row['sort_order'],
                            'alt_text' => $row['alt_text'] ?? null,
                        ]);
                }

                foreach ($deleteIds as $imageId) {
                    $image = $project->images()->whereKey($imageId)->firstOrFail();
                    $deletedPaths[] = $image->image_path;
                    $image->delete();
                }

                $sortOrder = (int) $project->images()->max('sort_order') + 1;
                $this->storeImages($project, $uploads, $uploadedPaths, $sortOrder);
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($uploadedPaths);
            throw $exception;
        }

        Storage::disk('public')->delete($deletedPaths);

        return redirect()->route('admin.portfolio.edit', $project)->with('status', 'Portfolio project updated.');
    }

    public function destroy(PortfolioProject $project)
    {
        $imagePaths = $project->images()->pluck('image_path')->all();

        DB::transaction(function () use ($project) {
            $project->delete();
        });
        Storage::disk('public')->delete($imagePaths);

        return redirect()->route('admin.portfolio')->with('status', 'Portfolio project deleted.');
    }

    private function form(PortfolioProject $project, string $formAction, string $formMethod)
    {
        return view('admin.portfolio.form', [
            'project' => $project,
            'formAction' => $formAction,
            'formMethod' => $formMethod,
        ]);
    }

    private function validateProject(Request $request, bool $creating): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
            'is_sample' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'images' => [$creating ? 'required' : 'nullable', 'array', 'min:' . ($creating ? 1 : 0), 'max:10'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'gallery' => ['nullable', 'array', 'max:100'],
            'gallery.*.sort_order' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'gallery.*.alt_text' => ['nullable', 'string', 'max:255'],
            'delete_images' => ['nullable', 'array', 'max:100'],
            'delete_images.*' => ['required', 'integer', 'distinct'],
        ]);
    }

    private function storeImages(PortfolioProject $project, array $uploads, array &$uploadedPaths, int $sortOrder): void
    {
        foreach ($uploads as $file) {
            $path = $file->store('portfolio', 'public');
            if (!$path) {
                throw new \RuntimeException('The portfolio photo could not be saved.');
            }

            $uploadedPaths[] = $path;
            PortfolioImage::create([
                'portfolio_project_id' => $project->id,
                'image_path' => $path,
                'alt_text' => Str::limit(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 255, ''),
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    private function uniqueSlug(string $title, ?PortfolioProject $project = null): string
    {
        $baseSlug = Str::slug($title) ?: 'portfolio-project';
        $slug = $baseSlug;
        $suffix = 2;

        while (PortfolioProject::where('slug', $slug)
            ->when($project, function ($query) use ($project) {
                $query->where('id', '!=', $project->id);
            })
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
