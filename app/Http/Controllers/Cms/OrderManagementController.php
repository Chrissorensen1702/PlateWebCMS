<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\CustomerSolution;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\Sales\PricingPackageCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class OrderManagementController extends Controller
{
    public function index(Request $request, PricingPackageCatalog $pricingPackageCatalog): View
    {
        abort_unless($request->user()?->isDeveloper(), 403);

        $plans = Plan::query()
            ->active()
            ->ordered()
            ->with('featureItems')
            ->get();

        $packageMap = $pricingPackageCatalog->packageMap($plans);
        $orders = CustomerSolution::query()
            ->with([
                'plan',
                'user.tenants' => fn ($query) => $query->with(['sites.plan', 'users']),
            ])
            ->latest()
            ->get()
            ->map(function (CustomerSolution $solution) use ($pricingPackageCatalog, $packageMap): array {
                $resolved = $pricingPackageCatalog->resolveSelection($packageMap, $solution->toArray());
                $tenant = $this->primaryTenantFor($solution->user?->tenants ?? collect());
                $site = $tenant?->sites
                    ?->firstWhere('plan_id', $solution->plan_id)
                    ?? $tenant?->sites?->first();

                return [
                    'solution' => $solution,
                    'user' => $solution->user,
                    'tenant' => $tenant,
                    'site' => $site,
                    'package_title' => $resolved['title'] ?? $solution->package_key,
                    'price' => $resolved['price'] ?? null,
                    'price_note' => $resolved['price_note'] ?? null,
                    'selection_summary' => $this->selectionSummary($resolved),
                    'status' => $this->statusMeta($tenant, $site),
                ];
            });

        return view('cms.pages.orders.index', [
            'orders' => $orders,
        ]);
    }

    /**
     * @param  Collection<int, Tenant>  $tenants
     */
    private function primaryTenantFor(Collection $tenants): ?Tenant
    {
        return $tenants
            ->sortBy(fn (Tenant $tenant): int => match ($tenant->pivot?->role) {
                'owner' => 0,
                'editor' => 1,
                default => 2,
            })
            ->first();
    }

    /**
     * @param  array<string, mixed>  $resolved
     */
    private function selectionSummary(array $resolved): string
    {
        $summaryMap = [
            'locations' => ($resolved['locations'] ?? 0).' lokationer',
            'staff' => ($resolved['staff'] ?? 0).' medarbejdere',
            'bookings' => number_format((int) ($resolved['bookings'] ?? 0), 0, ',', '.').' bookinger/år',
            'sections' => ($resolved['sections'] ?? 0).(($resolved['package_key'] ?? null) === 'launch' ? ' sider' : ' sektioner'),
            'traffic_tier' => match ($resolved['traffic_tier'] ?? 'low') {
                'high' => 'høj trafik',
                'medium' => 'mellem trafik',
                default => 'lav trafik',
            },
            'lead_module' => ! empty($resolved['lead_module']) ? 'nyhedsbrev- og leadmodul' : null,
            'seo_copy' => ! empty($resolved['seo_copy']) ? 'professionel opsætning' : null,
        ];

        return collect($resolved['visible_fields'] ?? [])
            ->filter(fn (mixed $field): bool => is_string($field) && array_key_exists($field, $summaryMap) && filled($summaryMap[$field]))
            ->map(fn (string $field): string => (string) $summaryMap[$field])
            ->implode(' · ');
    }

    /**
     * @return array{label: string, tone: string}
     */
    private function statusMeta(?Tenant $tenant, mixed $site): array
    {
        if ($site !== null) {
            return [
                'label' => 'Klar i CMS',
                'tone' => 'success',
            ];
        }

        if ($tenant !== null) {
            return [
                'label' => 'Kunde oprettet',
                'tone' => 'info',
            ];
        }

        return [
            'label' => 'Afventer oprettelse',
            'tone' => 'muted',
        ];
    }
}
