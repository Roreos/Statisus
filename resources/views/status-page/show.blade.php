<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->name }} — Status</title>
    <meta name="description" content="{{ $page->description ?? $page->name . ' service status' }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --brand: {{ $page->primary_color }};
            --bg:    {{ $page->background_color }};
        }
        body { background-color: var(--bg); }
        .brand-text  { color: var(--brand); }
        .brand-bg    { background-color: var(--brand); }
        .brand-border{ border-color: var(--brand); }

        /* Sidebar sticks on desktop */
        @media (min-width: 1024px) {
            .sidebar { position: sticky; top: 0; height: 100vh; overflow-y: auto; }
        }

        /* Uptime bar */
        .uptime-track { display: flex; gap: 2px; height: 24px; }
        .bar-seg { flex: 1; border-radius: 2px; position: relative; cursor: default; }
        .bar-seg .tip {
            display: none;
            position: absolute;
            bottom: calc(100% + 5px);
            left: 50%;
            transform: translateX(-50%);
            white-space: nowrap;
            background: #1e293b;
            border: 1px solid #334155;
            color: #94a3b8;
            font-size: 11px;
            padding: 3px 7px;
            border-radius: 4px;
            pointer-events: none;
            z-index: 20;
        }
        .bar-seg:hover .tip { display: block; }

        /* Pulse dot */
        @keyframes pulse-ring {
            0%   { transform: scale(.85); opacity: .9; }
            70%  { transform: scale(1.8); opacity: 0; }
            100% { transform: scale(1.8); opacity: 0; }
        }
        .pulse-wrap { position: relative; display: inline-flex; align-items: center; justify-content: center; width: 10px; height: 10px; }
        .pulse-wrap .ring {
            position: absolute; inset: 0; border-radius: 9999px;
            animation: pulse-ring 2.2s ease-out infinite;
        }
        .pulse-wrap .dot { position: relative; z-index: 1; width: 10px; height: 10px; border-radius: 9999px; }

        /* Incident timeline line */
        .timeline-line { position: absolute; left: 5px; top: 20px; bottom: 0; width: 1px; background: #1e293b; }
    </style>
</head>
<body class="min-h-screen text-slate-300 antialiased">

{{-- Far-left icon nav --}}
@include('status-page.partials.nav', ['categories' => $categories, 'currentSlug' => $page->slug])

<div class="pl-14 lg:flex lg:min-h-screen">

    {{-- ════════════════════════════════════════
         LEFT SIDEBAR
    ════════════════════════════════════════ --}}
    <aside class="sidebar lg:w-72 xl:w-80 border-r border-slate-800 flex flex-col px-8 py-10 shrink-0">

        {{-- Logo / Name --}}
        <div class="mb-10">
            @if($page->logo_path)
                <img src="{{ Storage::url($page->logo_path) }}"
                     alt="{{ $page->name }}"
                     class="h-8 w-auto object-contain mb-4">
            @endif
            <h1 class="text-lg font-bold text-white leading-snug">{{ $page->name }}</h1>
            @if($page->description)
                <p class="text-slate-500 text-sm mt-1 leading-relaxed">{{ $page->description }}</p>
            @endif
        </div>

        {{-- Overall status --}}
        @php $overall = $page->overallStatus(); @endphp
        <div class="mb-10">
            <p class="text-xs uppercase tracking-widest text-slate-600 mb-3">Current status</p>

            <div class="flex items-center gap-3 mb-2">
                <span class="pulse-wrap shrink-0">
                    <span class="ring
                        @if($overall === 'up') bg-emerald-400
                        @elseif($overall === 'down') bg-rose-400
                        @elseif($overall === 'degraded') bg-amber-400
                        @else bg-slate-500 @endif"></span>
                    <span class="dot
                        @if($overall === 'up') bg-emerald-400
                        @elseif($overall === 'down') bg-rose-400
                        @elseif($overall === 'degraded') bg-amber-400
                        @else bg-slate-500 @endif"></span>
                </span>
                <span class="text-base font-semibold
                    @if($overall === 'up') text-emerald-400
                    @elseif($overall === 'down') text-rose-400
                    @elseif($overall === 'degraded') text-amber-400
                    @else text-slate-400 @endif">
                    @if($overall === 'up') Operational
                    @elseif($overall === 'down') Major Outage
                    @elseif($overall === 'degraded') Degraded
                    @else Checking… @endif
                </span>
            </div>

            @php
                $latestUpdate = collect($incidentsByMonitor)
                    ->flatten(1)
                    ->where('status', 'open')
                    ->flatMap(fn($i) => $i->updates)
                    ->sortByDesc('posted_at')
                    ->first();
            @endphp
            <p class="text-xs text-slate-600 pl-[22px]">
                @if($overall === 'up') All services running normally.
                @elseif($latestUpdate) {{ $latestUpdate->message }}
                @elseif($overall === 'down') We are actively investigating.
                @elseif($overall === 'degraded') Some services are affected.
                @else Initial checks in progress. @endif
            </p>
        </div>

        {{-- Quick stats --}}
        @php
            $totalMonitors = $page->monitors->count();
            $upCount       = $page->monitors->where('status', 'up')->count();
            $downCount     = $page->monitors->where('status', 'down')->count();
            $degradedCount = $page->monitors->where('status', 'degraded')->count();
        @endphp
        @if($totalMonitors > 0)
            <div class="mb-10 space-y-3">
                <p class="text-xs uppercase tracking-widest text-slate-600 mb-3">At a glance</p>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Operational</span>
                    <span class="text-emerald-400 font-medium tabular-nums">{{ $upCount }} / {{ $totalMonitors }}</span>
                </div>
                @if($downCount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Down</span>
                        <span class="text-rose-400 font-medium tabular-nums">{{ $downCount }}</span>
                    </div>
                @endif
                @if($degradedCount > 0)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-500">Degraded</span>
                        <span class="text-amber-400 font-medium tabular-nums">{{ $degradedCount }}</span>
                    </div>
                @endif

                {{-- Mini progress bar --}}
                @if($totalMonitors > 0)
                    <div class="flex gap-0.5 h-1.5 rounded-full overflow-hidden mt-2">
                        @if($upCount > 0)
                            <div class="bg-emerald-500 rounded-full" style="width:{{ ($upCount/$totalMonitors)*100 }}%"></div>
                        @endif
                        @if($degradedCount > 0)
                            <div class="bg-amber-500 rounded-full" style="width:{{ ($degradedCount/$totalMonitors)*100 }}%"></div>
                        @endif
                        @if($downCount > 0)
                            <div class="bg-rose-500 rounded-full" style="width:{{ ($downCount/$totalMonitors)*100 }}%"></div>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        {{-- Active maintenance notice --}}
        @if($page->show_maintenance && $maintenance->where(fn($w) => $w->isActive())->isNotEmpty())
            <div class="mb-10 rounded-lg border border-amber-800/50 bg-amber-950/30 px-4 py-3">
                <p class="text-xs font-semibold text-amber-400 mb-1">Maintenance in progress</p>
                @foreach($maintenance->where(fn($w) => $w->isActive()) as $w)
                    <p class="text-xs text-slate-400">{{ $w->title }}</p>
                @endforeach
            </div>
        @endif

        {{-- Spacer pushes footer down --}}
        <div class="flex-1"></div>

        {{-- Footer --}}
        <div class="pt-6 border-t border-slate-800 space-y-1">
            <p class="text-xs text-slate-600">Updated {{ now()->format('M j, Y · H:i') }} UTC</p>
            <p class="text-xs text-slate-700">
                Powered by <span class="brand-text font-medium">Status Monitor</span>
            </p>
            <p class="text-xs text-slate-700">&copy; {{ date('Y') }} {{ $page->name }}</p>
        </div>
    </aside>

    {{-- ════════════════════════════════════════
         MAIN CONTENT
    ════════════════════════════════════════ --}}
    <main class="flex-1 px-8 lg:px-12 py-10 space-y-14 min-w-0">

        {{-- ── Upcoming Maintenance ── --}}
        @if($page->show_maintenance && $maintenance->where(fn($w) => $w->isUpcoming())->isNotEmpty())
            <section>
                <h2 class="text-xs uppercase tracking-widest text-slate-600 mb-5">Scheduled Maintenance</h2>
                <div class="space-y-3">
                    @foreach($maintenance->where(fn($w) => $w->isUpcoming()) as $window)
                        <div class="flex items-start gap-4 rounded-lg border border-slate-800 px-5 py-4">
                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white">{{ $window->title }}</p>
                                @if($window->description)
                                    <p class="text-xs text-slate-500 mt-1">{{ $window->description }}</p>
                                @endif
                                <p class="text-xs text-slate-600 mt-2">
                                    {{ $window->scheduled_at->format('M j, Y · H:i') }} – {{ $window->ends_at->format('H:i') }} UTC
                                </p>
                            </div>
                            <span class="shrink-0 text-xs text-amber-400 border border-amber-800/60 px-2 py-0.5 rounded">Upcoming</span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ── Services ── --}}
        <section>
            <h2 class="text-xs uppercase tracking-widest text-slate-600 mb-5">Services</h2>

            @if($page->monitors->isEmpty())
                <p class="text-slate-600 text-sm">No services configured.</p>
            @else
                <div class="space-y-0 divide-y divide-slate-800/60">
                    @foreach($page->monitors as $monitor)
                        @php
                            $displayName  = $monitor->pivot->display_name ?: $monitor->name;
                            $uptime       = $monitor->uptimePercentage(24);
                            $recentChecks = $monitor->recentChecks(60);
                            $avgMs        = $monitor->avgResponseTime(24);
                        @endphp
                        <div class="py-5 first:pt-0 last:pb-0">

                            {{-- Service header row --}}
                            <div class="flex items-center justify-between gap-4 mb-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="pulse-wrap shrink-0">
                                        <span class="ring
                                            @if($monitor->status === 'up') bg-emerald-400
                                            @elseif($monitor->status === 'down') bg-rose-400
                                            @elseif($monitor->status === 'degraded') bg-amber-400
                                            @else bg-slate-500 @endif"></span>
                                        <span class="dot
                                            @if($monitor->status === 'up') bg-emerald-400
                                            @elseif($monitor->status === 'down') bg-rose-400
                                            @elseif($monitor->status === 'degraded') bg-amber-400
                                            @else bg-slate-500 @endif"></span>
                                    </span>
                                    <span class="font-medium text-white text-sm truncate">{{ $displayName }}</span>
                                    <span class="text-xs text-slate-700 shrink-0 hidden sm:inline">{{ $monitor->typeLabel() }}</span>
                                </div>

                                <div class="flex items-center gap-4 shrink-0 text-xs text-slate-500">
                                    @if($avgMs)
                                        <span class="hidden sm:inline tabular-nums">{{ round($avgMs) }}ms avg</span>
                                    @endif
                                    @if($page->show_uptime_graph)
                                        <span class="tabular-nums">{{ $uptime }}% uptime</span>
                                    @endif
                                    <span class="font-medium
                                        @if($monitor->status === 'up') text-emerald-400
                                        @elseif($monitor->status === 'down') text-rose-400
                                        @elseif($monitor->status === 'degraded') text-amber-400
                                        @else text-slate-500 @endif">
                                        {{ ucfirst($monitor->status) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Uptime bar --}}
                            @if($page->show_uptime_graph && $recentChecks->isNotEmpty())
                                <div class="uptime-track">
                                    @foreach($recentChecks->reverse() as $check)
                                        <div class="bar-seg
                                            @if($check->status === 'up') bg-emerald-600
                                            @elseif($check->status === 'down') bg-rose-600
                                            @elseif($check->status === 'degraded') bg-amber-600
                                            @else bg-slate-700 @endif">
                                            <span class="tip">
                                                {{ $check->checked_at->format('M j H:i') }}
                                                · {{ ucfirst($check->status) }}
                                                @if($check->response_time) · {{ $check->response_time }}ms @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="flex justify-between mt-1">
                                    <span class="text-xs text-slate-700">60 checks ago</span>
                                    <span class="text-xs text-slate-700">now</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ── Incident History ── --}}
        @if($page->show_incidents)
            <section>
                <h2 class="text-xs uppercase tracking-widest text-slate-600 mb-5">
                    Incident History <span class="normal-case font-normal text-slate-700 ml-1">— past 90 days</span>
                </h2>
                @php
                    $allIncidents = collect($incidentsByMonitor)->flatten(1)->sortByDesc('started_at');
                @endphp
                @if($allIncidents->isEmpty())
                    <div class="flex items-center gap-3 py-6 border-t border-slate-800">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-slate-500">No incidents in the past 90 days.</p>
                    </div>
                @else
                    <div class="relative pl-4">
                        {{-- Vertical timeline line --}}
                        <div class="timeline-line"></div>

                        <div class="space-y-0">
                            @foreach($allIncidents as $incident)
                                @php $monitor = $page->monitors->firstWhere('id', $incident->monitor_id); @endphp
                                <div class="relative pb-8 last:pb-0">
                                    {{-- Timeline node --}}
                                    <span class="absolute -left-[11px] top-1.5 w-3 h-3 rounded-full border-2
                                        {{ $incident->status === 'resolved'
                                            ? 'bg-slate-900 border-emerald-600'
                                            : 'bg-rose-500 border-rose-400' }}">
                                    </span>

                                    <div class="pl-5">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-medium text-white">
                                                    {{ $monitor?->pivot->display_name ?: $monitor?->name ?? 'Unknown service' }}
                                                </p>
                                                <p class="text-xs text-slate-500 mt-0.5">
                                                    {{ $incident->cause ?? 'Service disruption' }}
                                                </p>
                                            </div>
                                            <span class="shrink-0 text-xs font-medium
                                                {{ $incident->status === 'resolved' ? 'text-emerald-500' : 'text-rose-400' }}">
                                                {{ ucfirst($incident->status) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-slate-700 mt-1.5">
                                            {{ $incident->started_at->format('M j, Y · H:i') }} UTC
                                            @if($incident->resolved_at)
                                                · {{ $incident->durationForHumans() }}
                                            @else
                                                · <span class="text-rose-500">Ongoing</span>
                                            @endif
                                        </p>

                                        {{-- Update timeline --}}
                                        @if($incident->updates->isNotEmpty())
                                            <div class="mt-3 space-y-2 border-l border-slate-800 pl-3">
                                                @foreach($incident->updates as $update)
                                                    <div>
                                                        <span class="text-xs font-medium
                                                            @if($update->status === 'investigating') text-rose-400
                                                            @elseif($update->status === 'identified') text-amber-400
                                                            @elseif($update->status === 'monitoring') text-sky-400
                                                            @else text-emerald-400 @endif">
                                                            {{ $update->statusLabel() }}
                                                        </span>
                                                        <span class="text-slate-600 text-xs mx-1">·</span>
                                                        <span class="text-xs text-slate-600">{{ $update->posted_at->format('M j, H:i') }} UTC</span>
                                                        <p class="text-xs text-slate-400 mt-0.5">{{ $update->message }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        @endif

        {{-- Mobile footer (sidebar footer is hidden on mobile) --}}
        <footer class="lg:hidden pt-6 border-t border-slate-800 space-y-1">
            <p class="text-xs text-slate-600">Updated {{ now()->format('M j, Y · H:i') }} UTC</p>
            <p class="text-xs text-slate-700">Powered by <span class="brand-text font-medium">Status Monitor</span></p>
            <p class="text-xs text-slate-700">&copy; {{ date('Y') }} {{ $page->name }}</p>
        </footer>

    </main>
</div>

</body>
</html>
