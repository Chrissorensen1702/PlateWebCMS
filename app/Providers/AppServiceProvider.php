<?php

namespace App\Providers;

use App\Models\Site;
use App\Policies\SitePolicy;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Site::class, SitePolicy::class);

        View::composer('cms.layouts.navigation', function ($view): void {
            $user = Auth::user();

            if (! $user) {
                $view->with('navPlanChip', null);

                return;
            }

            $currentSite = request()->route('site');

            if ($currentSite instanceof Site) {
                $currentSite->loadMissing('plan');

                $view->with('navPlanChip', [
                    'label' => 'Abonnement',
                    'value' => $currentSite->plan?->name ?? 'Ingen plan',
                ]);

                return;
            }

            $visibleSites = Site::query()
                ->visibleTo($user)
                ->with('plan:id,name')
                ->orderBy('name')
                ->get(['id', 'name', 'plan_id', 'tenant_id']);

            if ($visibleSites->isEmpty()) {
                if ($user->isDeveloper()) {
                    $view->with('navPlanChip', null);

                    return;
                }

                $user->loadMissing('pricingSolution.plan');

                $view->with('navPlanChip', [
                    'label' => 'Abonnement',
                    'value' => $this->packageLabel((string) $user->pricingSolution?->package_key)
                        ?? $user->pricingSolution?->plan?->name
                        ?? 'Ingen plan endnu',
                ]);

                return;
            }

            $uniquePlanLabels = $visibleSites
                ->map(fn (Site $site): string => $site->plan?->name ?? 'Ingen plan')
                ->unique()
                ->values();

            $view->with('navPlanChip', [
                'label' => 'Abonnement',
                'value' => $uniquePlanLabels->count() === 1
                    ? $uniquePlanLabels->first()
                    : $uniquePlanLabels->count().' planer',
            ]);
        });
    }

    private function packageLabel(string $packageKey): ?string
    {
        return match ($packageKey) {
            'launch' => 'Atelier',
            'scale' => 'Studio',
            'signature' => 'Signature',
            'platebook' => 'Chairflow',
            default => null,
        };
    }
}
