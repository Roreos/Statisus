{{-- Expects $statusPage (StatusPage with monitors loaded) --}}
@php
    $overall = $statusPage->overallStatus();
@endphp
<a href="{{ route('status-page.show', $statusPage->slug) }}"
   class="flex items-center justify-between gap-4 py-4 first:pt-0 last:pb-0 hover:text-white transition-colors group">
    <div class="flex items-center gap-3 min-w-0">
        <span class="shrink-0 w-2 h-2 rounded-full
            @if($overall === 'up') bg-emerald-500
            @elseif($overall === 'down') bg-rose-500
            @elseif($overall === 'degraded') bg-amber-500
            @else bg-slate-600 @endif">
        </span>
        <span class="text-sm font-medium text-slate-200 group-hover:text-white truncate">
            {{ $statusPage->name }}
        </span>
        @if($statusPage->description)
            <span class="text-xs text-slate-600 truncate hidden sm:inline">{{ $statusPage->description }}</span>
        @endif
    </div>
    <div class="flex items-center gap-3 shrink-0">
        <span class="text-xs
            @if($overall === 'up') text-emerald-500
            @elseif($overall === 'down') text-rose-400
            @elseif($overall === 'degraded') text-amber-400
            @else text-slate-500 @endif">
            @if($overall === 'up') Operational
            @elseif($overall === 'down') Outage
            @elseif($overall === 'degraded') Degraded
            @else Pending @endif
        </span>
        <svg class="w-3.5 h-3.5 text-slate-700 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
    </div>
</a>
