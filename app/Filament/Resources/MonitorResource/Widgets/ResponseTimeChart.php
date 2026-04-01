<?php

namespace App\Filament\Resources\MonitorResource\Widgets;

use App\Models\MonitorCheck;
use Filament\Widgets\ChartWidget;

class ResponseTimeChart extends ChartWidget
{
    public ?string $heading = 'Response Time (last 50 checks)';

    protected int|string|array $columnSpan = 'full';

    public ?int $monitorId = null;

    public function mount(): void
    {
        $this->monitorId = request()->route('record');
    }

    protected function getData(): array
    {
        $checks = MonitorCheck::query()
            ->where('monitor_id', $this->monitorId)
            ->whereNotNull('response_time')
            ->latest('checked_at')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return [
            'datasets' => [
                [
                    'label'           => 'Response Time (ms)',
                    'data'            => $checks->pluck('response_time')->toArray(),
                    'borderColor'     => 'rgb(6, 182, 212)',
                    'backgroundColor' => 'rgba(6, 182, 212, 0.1)',
                    'fill'            => true,
                    'tension'         => 0.3,
                    'pointRadius'     => 2,
                ],
            ],
            'labels' => $checks->map(fn ($c) => $c->checked_at->format('H:i:s'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title'       => ['display' => true, 'text' => 'ms'],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
        ];
    }
}
