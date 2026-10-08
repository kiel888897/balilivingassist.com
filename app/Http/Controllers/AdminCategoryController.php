<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminCategoryController extends Controller
{
    public function index()
    {
        return $this->indexForType('shop');
    }

    public function create()
    {
        return $this->createForType('shop');
    }

    public function store(Request $request)
    {
        return $this->storeForType($request, 'shop');
    }

    public function edit(Category $category)
    {
        return $this->editForType($category, 'shop');
    }

    public function update(Request $request, Category $category)
    {
        return $this->updateForType($request, $category, 'shop');
    }

    public function destroy(Category $category)
    {
        return $this->destroyForType($category, 'shop');
    }

    public function rentalIndex()
    {
        return $this->indexForType('rental');
    }

    public function rentalCreate()
    {
        return $this->createForType('rental');
    }

    public function rentalStore(Request $request)
    {
        return $this->storeForType($request, 'rental');
    }

    public function rentalEdit(Category $category)
    {
        return $this->editForType($category, 'rental');
    }

    public function rentalUpdate(Request $request, Category $category)
    {
        return $this->updateForType($request, $category, 'rental');
    }

    public function rentalDestroy(Category $category)
    {
        return $this->destroyForType($category, 'rental');
    }

    private function indexForType(string $type)
    {
        $categories = Category::withCount('products')
            ->where('type', $type)
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
            'categoryType' => $type,
            'categoryLabel' => ucfirst($type),
            'categoryRoute' => $this->categoryRoute($type),
        ]);
    }

    private function createForType(string $type)
    {
        return $this->form(new Category(['type' => $type]), route($this->categoryRoute($type) . '.store'), 'POST', $type);
    }

    private function storeForType(Request $request, string $type)
    {
        $data = $this->validateCategory($request, $type);
        $data['type'] = $type;
        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return redirect()->route($this->categoryRoute($type))->with('status', ucfirst($type) . ' category created.');
    }

    private function editForType(Category $category, string $type)
    {
        abort_unless($category->type === $type, 404);

        return $this->form(
            $category,
            route($this->categoryRoute($type) . '.update', $category),
            'PUT',
            $type
        );
    }

    private function updateForType(Request $request, Category $category, string $type)
    {
        abort_unless($category->type === $type, 404);
        $data = $this->validateCategory($request, $type);
        $data['type'] = $type;
        $data['slug'] = $this->uniqueSlug($data['name'], $category);

        if ($request->boolean('remove_image') && $category->image_path) {
            Storage::disk('public')->delete($category->image_path);
            $data['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            $oldImagePath = $category->image_path;
            $data['image_path'] = $request->file('image')->store('categories', 'public');

            if ($oldImagePath) {
                Storage::disk('public')->delete($oldImagePath);
            }
        }

        $category->update($data);

        return redirect()->route($this->categoryRoute($type))->with('status', ucfirst($type) . ' category updated.');
    }

    private function destroyForType(Category $category, string $type)
    {
        abort_unless($category->type === $type, 404);

        if ($category->products()->exists()) {
            return redirect()->route($this->categoryRoute($type))
                ->with('error', 'This category is in use. Move or remove its items before deleting it.');
        }

        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }

        $category->delete();

        return redirect()->route($this->categoryRoute($type))->with('status', ucfirst($type) . ' category deleted.');
    }

    private function form(Category $category, string $formAction, string $formMethod, string $type)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'formAction' => $formAction,
            'formMethod' => $formMethod,
            'categoryType' => $type,
            'categoryLabel' => ucfirst($type),
            'categoryRoute' => $this->categoryRoute($type),
        ]);
    }

    private function validateCategory(Request $request, string $type): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in([$type])],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);
    }

    private function categoryRoute(string $type): string
    {
        if ($type === 'rental') {
            return 'admin.rental-categories';
        }

        return 'admin.categories';
    }

    private function uniqueSlug(string $name, ?Category $category = null): string
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::where('slug', $slug)
            ->when($category, function ($query) use ($category) {
                $query->where('id', '!=', $category->id);
            })
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }
}
