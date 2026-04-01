{{--
    Far-left icon nav strip.
    Expects: $categories (collection), $currentSlug (optional string)
--}}
<nav class="fixed top-0 left-0 h-full w-14 flex flex-col items-center py-4 z-30"
     style="background-color: color-mix(in srgb, var(--bg) 80%, #000);">

    {{-- Home / index --}}
    <a href="{{ route('status-page.index') }}"
       title="All status pages"
       class="mb-6 w-9 h-9 flex items-center justify-center rounded-lg transition-colors
              {{ !isset($currentSlug) ? 'brand-bg text-white' : 'text-slate-600 hover:text-slate-300 hover:bg-slate-800' }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
        </svg>
    </a>

    {{-- Divider --}}
    <div class="w-5 h-px bg-slate-800 mb-4"></div>

    {{-- Category buttons --}}
    @foreach($categories as $category)
        @php
            // Is any page in this category the current one?
            $active = isset($currentSlug) && $category->publicPages->contains('slug', $currentSlug);
        @endphp
        <a href="{{ route('status-page.index') }}#cat-{{ $category->slug }}"
           title="{{ $category->name }}"
           class="mb-2 w-9 h-9 flex items-center justify-center rounded-lg transition-colors
                  {{ $active ? 'brand-bg text-white' : 'text-slate-600 hover:text-slate-300 hover:bg-slate-800' }}">
            @include('status-page.partials.category-icon', ['icon' => $category->icon])
        </a>
    @endforeach

    {{-- Spacer --}}
    <div class="flex-1"></div>

    {{-- Login --}}
    <a href="{{ url('/admin/login') }}"
       title="Admin login"
       class="w-9 h-9 flex items-center justify-center rounded-lg text-slate-700 hover:text-slate-400 hover:bg-slate-800 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
        </svg>
    </a>
</nav>
