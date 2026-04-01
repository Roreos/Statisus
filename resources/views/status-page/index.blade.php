<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pages</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --brand: #0ea5e9; --bg: #0f172a; }
        body  { background-color: var(--bg); }
        .brand-bg   { background-color: var(--brand); }
        .brand-text { color: var(--brand); }
    </style>
</head>
<body class="min-h-screen text-slate-300 antialiased">

{{-- Far-left nav --}}
@include('status-page.partials.nav', ['categories' => $categories])

<div class="pl-14">
    <div class="max-w-3xl mx-auto px-8 py-12">

        <h1 class="text-2xl font-bold text-white mb-1">Status Pages</h1>
        <p class="text-slate-500 text-sm mb-10">Current status across all services.</p>

        {{-- Categories --}}
        @foreach($categories as $category)
            @if($category->publicPages->isNotEmpty())
                <section id="cat-{{ $category->slug }}" class="mb-12">
                    <div class="flex items-center gap-2 mb-5">
                        <span class="text-slate-500">
                            @include('status-page.partials.category-icon', ['icon' => $category->icon])
                        </span>
                        <h2 class="text-xs uppercase tracking-widest text-slate-500">{{ $category->name }}</h2>
                    </div>

                    <div class="space-y-0 divide-y divide-slate-800/60">
                        @foreach($category->publicPages as $statusPage)
                            @include('status-page.partials.page-row', ['statusPage' => $statusPage])
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        {{-- Uncategorised --}}
        @if($uncategorised->isNotEmpty())
            <section class="mb-12">
                <h2 class="text-xs uppercase tracking-widest text-slate-600 mb-5">Other</h2>
                <div class="space-y-0 divide-y divide-slate-800/60">
                    @foreach($uncategorised as $statusPage)
                        @include('status-page.partials.page-row', ['statusPage' => $statusPage])
                    @endforeach
                </div>
            </section>
        @endif

        @if($categories->isEmpty() && $uncategorised->isEmpty())
            <p class="text-slate-600 text-sm">No public status pages yet.</p>
        @endif

    </div>

    <footer class="max-w-3xl mx-auto px-8 py-8 border-t border-slate-800 flex justify-between">
        <p class="text-xs text-slate-700">&copy; {{ date('Y') }}</p>
        <p class="text-xs text-slate-700">Powered by <span class="brand-text font-medium">Status Monitor</span></p>
    </footer>
</div>

</body>
</html>
