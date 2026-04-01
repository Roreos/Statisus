<?php

namespace App\Http\Controllers;

use App\Models\StatusPage;
use App\Models\StatusPageCategory;
use Illuminate\Http\Request;

class StatusPageController extends Controller
{
    /** /status — category index */
    public function index()
    {
        $categories = StatusPageCategory::with(['publicPages.monitors'])
            ->orderBy('sort_order')
            ->get();

        // Pages with no category
        $uncategorised = StatusPage::where('is_public', true)
            ->whereNull('category_id')
            ->with('monitors')
            ->get();

        return view('status-page.index', compact('categories', 'uncategorised'));
    }

    /** /status/{slug} */
    public function show(Request $request, string $slug)
    {
        $page = StatusPage::where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return $this->renderPage($page);
    }

    /** Custom domain routing */
    public function showByDomain(Request $request)
    {
        $page = StatusPage::where('custom_domain', $request->getHost())
            ->where('is_public', true)
            ->firstOrFail();

        return $this->renderPage($page);
    }

    private function renderPage(StatusPage $page)
    {
        $page->load(['monitors', 'category']);

        $categories = StatusPageCategory::with(['publicPages'])
            ->orderBy('sort_order')
            ->get();

        $incidentsByMonitor = [];
        if ($page->show_incidents) {
            foreach ($page->monitors as $monitor) {
                $incidentsByMonitor[$monitor->id] = $monitor->incidents()
                    ->with('updates')
                    ->where('started_at', '>=', now()->subDays(90))
                    ->orderByDesc('started_at')
                    ->get();
            }
        }

        $maintenance = $page->show_maintenance
            ? $page->upcomingMaintenance()->get()
            : collect();

        return view('status-page.show', compact('page', 'categories', 'incidentsByMonitor', 'maintenance'));
    }
}
