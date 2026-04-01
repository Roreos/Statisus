<x-filament-panels::page>
@php extract($this->getViewData()); @endphp

<div style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- ── Identity card (plain div, no section wrapper fighting the cover) ── --}}
    <div style="border-radius:.75rem;overflow:hidden;border:1px solid rgba(255,255,255,.08);background:#1e2535;">

        {{-- Cover --}}
        <div style="height:7rem;background:#0f172a;border-bottom:1px solid rgba(255,255,255,.08);"></div>

        <div style="padding:0 1.75rem 1.75rem;">
            {{-- Avatar + edit button --}}
            <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-top:-3rem;margin-bottom:1.25rem;">
                @if($user->avatar_url)
                    <img src="{{ asset('storage/' . $user->avatar_url) }}"
                         alt="{{ $user->name }}"
                         style="width:6rem;height:6rem;border-radius:9999px;object-fit:cover;border:4px solid #1e2535;display:block;flex-shrink:0;">
                @else
                    <div style="width:6rem;height:6rem;border-radius:9999px;background:#0891b2;border:4px solid #1e2535;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:1.75rem;font-weight:700;color:#fff;line-height:1;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    </div>
                @endif

                <x-filament::button
                    href="{{ route('filament.admin.auth.profile') }}"
                    tag="a"
                    color="gray"
                    size="sm"
                    outlined>
                    Edit profile
                </x-filament::button>
            </div>

            <p style="font-size:1.25rem;font-weight:700;color:#f9fafb;margin:0 0 .5rem;">{{ $user->name }}</p>

            <div style="display:flex;flex-wrap:wrap;gap:.25rem 1.5rem;font-size:.875rem;color:#9ca3af;">
                @if($user->job_title)
                    <span>{{ $user->job_title }}</span>
                @endif
                <span>{{ $user->email }}</span>
                <span>{{ $user->timezone ?? 'UTC' }}</span>
                <span>Member since {{ $user->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>

    {{-- ── Stats grid ── --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
        @foreach([
            ['Monitors',       $totalMonitors],
            ['Up now',         $upMonitors],
            ['Down now',       $downMonitors],
            ['Open incidents', $openIncidents],
            ['Alerts sent',    $alertsSent],
            ['Status pages',   $statusPages],
        ] as [$label, $value])
            <div style="border-radius:.75rem;border:1px solid rgba(255,255,255,.08);background:#1e2535;padding:1.25rem 1.5rem;">
                <p style="font-size:.75rem;color:#9ca3af;margin:0 0 .5rem;text-transform:uppercase;letter-spacing:.05em;">{{ $label }}</p>
                <p style="font-size:2rem;font-weight:700;color:#f9fafb;margin:0;font-variant-numeric:tabular-nums;">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Activity ── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">

        {{-- Recent alerts --}}
        <div style="border-radius:.75rem;border:1px solid rgba(255,255,255,.08);background:#1e2535;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.06);">
                <span style="font-size:.875rem;font-weight:600;color:#f9fafb;">Recent Alerts</span>
                <a href="{{ \App\Filament\Resources\AlertLogResource::getUrl('index') }}"
                   style="font-size:.75rem;color:#22d3ee;text-decoration:none;">View all →</a>
            </div>

            @if($recentAlerts->isEmpty())
                <p style="font-size:.875rem;color:#6b7280;text-align:center;padding:2rem;">No alerts yet.</p>
            @else
                @foreach($recentAlerts as $log)
                    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.75rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.04);">
                        <span style="margin-top:.375rem;flex-shrink:0;width:.5rem;height:.5rem;border-radius:9999px;background:{{ $log->success ? '#22c55e' : '#ef4444' }};"></span>
                        <div style="flex:1;min-width:0;">
                            <p style="font-size:.875rem;color:#e5e7eb;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $log->monitor?->name ?? '—' }}
                                <span style="color:#6b7280;font-weight:400;">via {{ $log->channel?->name ?? '—' }}</span>
                            </p>
                            <p style="font-size:.75rem;color:#6b7280;margin:.125rem 0 0;">
                                {{ ucfirst($log->event) }} · {{ $log->sent_at?->diffForHumans() ?? '—' }}
                            </p>
                        </div>
                        @if(!$log->success)
                            <x-filament::badge color="danger" size="sm">Failed</x-filament::badge>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        {{-- Recent incidents --}}
        <div style="border-radius:.75rem;border:1px solid rgba(255,255,255,.08);background:#1e2535;overflow:hidden;">
            <div style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.06);">
                <span style="font-size:.875rem;font-weight:600;color:#f9fafb;">Recent Incidents</span>
                <a href="{{ \App\Filament\Resources\IncidentResource::getUrl('index') }}"
                   style="font-size:.75rem;color:#22d3ee;text-decoration:none;">View all →</a>
            </div>

            @if($recentIncidents->isEmpty())
                <p style="font-size:.875rem;color:#6b7280;text-align:center;padding:2rem;">No incidents recorded.</p>
            @else
                @foreach($recentIncidents as $incident)
                    <div style="display:flex;align-items:flex-start;gap:.75rem;padding:.75rem 1.5rem;border-bottom:1px solid rgba(255,255,255,.04);">
                        <span style="margin-top:.375rem;flex-shrink:0;width:.5rem;height:.5rem;border-radius:9999px;background:{{ $incident->status === 'resolved' ? '#22c55e' : '#ef4444' }};"></span>
                        <div style="flex:1;min-width:0;">
                            <p style="font-size:.875rem;color:#e5e7eb;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                {{ $incident->monitor?->name ?? '—' }}
                            </p>
                            <p style="font-size:.75rem;color:#6b7280;margin:.125rem 0 0;">
                                {{ $incident->cause ?? 'Service disruption' }} · {{ $incident->started_at->diffForHumans() }}
                            </p>
                        </div>
                        <x-filament::badge :color="$incident->status === 'resolved' ? 'success' : 'danger'" size="sm">
                            {{ ucfirst($incident->status) }}
                        </x-filament::badge>
                    </div>
                @endforeach
            @endif
        </div>

    </div>

</div>
</x-filament-panels::page>
