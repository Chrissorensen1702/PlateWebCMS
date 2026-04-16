<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CustomerSolution;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Site;
use App\Support\LaravelCloud\LaravelCloudOverview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the internal CMS overview.
     */
    public function __invoke(Request $request, LaravelCloudOverview $laravelCloudOverview): View
    {
        $user = $request->user();
        $plans = Plan::query()->active()->ordered()->get();
        $siteQuery = Site::query()
            ->visibleTo($user)
            ->with(['tenant.users', 'plan'])
            ->latest();

        $recentSites = (clone $siteQuery)
            ->take(5)
            ->get();

        $canViewLeads = $user->isDeveloper();
        $visibleSites = Site::query()->visibleTo($user);
        $siteCount = (clone $visibleSites)->count();
        $onlineSiteCount = (clone $visibleSites)->where('is_online', true)->count();
        $draftProjectCount = (clone $visibleSites)->where('is_online', false)->count();
        $draftPageCount = (clone $visibleSites)
            ->withCount('draftPages')
            ->get()
            ->sum('draft_pages_count');
        $draftAreaCount = (clone $visibleSites)
            ->with(['draftPages.areas'])
            ->get()
            ->sum(fn (Site $site) => $site->draftPages->sum(fn ($page) => $page->areas->count()));
        $laravelCloudPanels = $user->isDeveloper()
            ? $laravelCloudOverview->fetchPanels()
            : null;
        $stats = $canViewLeads
            ? [
                [
                    'label' => 'Aktive pakker',
                    'value' => $plans->count(),
                    'copy' => 'Pakker der er aktive paa salgssiden.',
                    'action' => $user->canManagePlans()
                        ? ['label' => 'Administrere', 'href' => route('cms.plans.index')]
                        : null,
                ],
                [
                    'label' => 'Henvendelser',
                    'value' => Lead::query()->count(),
                    'copy' => 'Beskeder og kontaktforesporgsler sendt fra salgssiden.',
                    'action' => ['label' => 'Se henvendelser', 'href' => route('cms.leads.index')],
                ],
                [
                    'label' => 'Bestillinger',
                    'value' => CustomerSolution::query()->count(),
                    'copy' => 'Gemte loesninger og selvbetjente pakkevalg.',
                    'action' => ['label' => 'Se bestillinger', 'href' => route('cms.orders.index')],
                ],
                [
                    'label' => 'Projekter',
                    'value' => $draftProjectCount,
                    'copy' => 'Sites der endnu ikke er sat online.',
                    'action' => ['label' => 'Se projekter', 'href' => route('cms.projects.index')],
                ],
                [
                    'label' => 'Websites',
                    'value' => $siteCount,
                    'copy' => 'Samlet antal websites i systemet.',
                ],
            ]
            : [
                [
                    'label' => 'Mine sites',
                    'value' => $siteCount,
                    'copy' => 'Sites du har adgang til i dine tenants.',
                ],
                [
                    'label' => 'Sider',
                    'value' => $draftPageCount,
                    'copy' => "Kladde-sider du kan redigere i CMS'et.",
                ],
                [
                    'label' => 'Online',
                    'value' => $onlineSiteCount,
                    'copy' => 'Af dine sites der er sat online.',
                ],
                [
                    'label' => 'Sideafsnit',
                    'value' => $draftAreaCount,
                    'copy' => 'Kladde-afsnit paa sider du kan arbejde i.',
                ],
            ];

        return view('cms.pages.dashboard', [
            'stats' => $stats,
            'plans' => $plans,
            'recentSites' => $recentSites,
            'canViewLeads' => $canViewLeads,
            'laravelCloudPanels' => $laravelCloudPanels,
        ]);
    }
}
