<nav class="grid gap-1" aria-label="{{ $categoryLabel }}">
    <a href="{{ route($categoryRoute, request()->except(['category', 'page'])) }}" class="flex items-center gap-3 rounded-lg px-2 py-2.5 text-sm {{ !$selectedCategory ? 'font-bold text-slate-900 underline underline-offset-4' : 'text-slate-700 hover:bg-slate-50' }}" @if (!$selectedCategory) aria-current="page" @endif>
        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full border {{ !$selectedCategory ? 'border-orange-600' : 'border-slate-300' }}">
            @if (!$selectedCategory)<span class="h-2.5 w-2.5 rounded-full bg-orange-600"></span>@endif
        </span>
        {{ $allCategoriesLabel }}
    </a>
    @foreach ($categories as $category)
    @php($isSelected = $selectedCategory === $category->slug)
    <a href="{{ route($categoryRoute, array_merge(request()->except(['category', 'page']), ['category' => $category->slug])) }}" class="flex items-center gap-3 rounded-lg px-2 py-2.5 text-sm {{ $isSelected ? 'font-bold text-slate-900 underline underline-offset-4' : 'text-slate-700 hover:bg-slate-50' }}" @if ($isSelected) aria-current="page" @endif>
        <span class="grid h-5 w-5 shrink-0 place-items-center rounded-full border {{ $isSelected ? 'border-orange-600' : 'border-slate-300' }}">
            @if ($isSelected)<span class="h-2.5 w-2.5 rounded-full bg-orange-600"></span>@endif
        </span>
        <span>{{ $category->name }}</span>
    </a>
    @endforeach
</nav>
