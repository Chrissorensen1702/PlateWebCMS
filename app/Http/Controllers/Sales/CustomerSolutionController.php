<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\CustomerSolution;
use App\Models\Plan;
use App\Models\User;
use App\Support\Sales\PricingPackageCatalog;
use App\Support\Sites\SelfServiceWebsiteProvisioner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerSolutionController extends Controller
{
    public const SESSION_KEY = 'sales.pending_customer_solution';

    public function store(
        Request $request,
        PricingPackageCatalog $pricingPackageCatalog,
        SelfServiceWebsiteProvisioner $selfServiceWebsiteProvisioner,
    ): RedirectResponse
    {
        $plans = $this->plans();
        $packageMap = $pricingPackageCatalog->packageMap($plans);

        $validated = $request->validate([
            'package_key' => ['required', 'string', 'in:'.implode(',', array_keys($packageMap))],
            'locations' => ['required', 'integer', 'min:1', 'max:10'],
            'staff' => ['required', 'integer', 'min:1', 'max:100'],
            'bookings' => ['required', 'integer', 'min:50', 'max:5000'],
            'sections' => ['required', 'integer', 'min:1', 'max:5'],
            'traffic_tier' => ['nullable', 'string', 'in:low,medium,high'],
            'lead_module' => ['nullable', 'boolean'],
            'seo_copy' => ['nullable', 'boolean'],
            'billing_cycle' => ['nullable', 'string', 'in:monthly,annual'],
        ]);

        $selection = $pricingPackageCatalog->normalizeSelection($validated, $packageMap);

        if ($request->user() instanceof User) {
            $solution = $this->persistSolution($request->user(), $selection);
            $selfServiceWebsiteProvisioner->provisionForPricingSolution($request->user(), $solution);

            return redirect()
                ->route('customer.solution.show')
                ->with('status', 'Din løsning er gemt på kontoen, så du kan vende tilbage til den når som helst.');
        }

        $request->session()->put(self::SESSION_KEY, $selection);

        return redirect()
            ->route('register')
            ->with('status', 'Din løsning er gemt. Opret konto for at fortsætte og vende tilbage senere.');
    }

    public function show(Request $request, PricingPackageCatalog $pricingPackageCatalog): View
    {
        $plans = $this->plans();
        $packageMap = $pricingPackageCatalog->packageMap($plans);
        $storedSolution = $request->user()?->pricingSolution;
        $selection = $storedSolution
            ? $pricingPackageCatalog->normalizeSelection($storedSolution->toArray(), $packageMap)
            : $pricingPackageCatalog->defaultSelection();
        $resolvedSolution = $pricingPackageCatalog->resolveSelection($packageMap, $selection);

        return view('cms.pages.customer-solution.show', [
            'hasSavedSolution' => $storedSolution !== null,
            'resolvedSolution' => $resolvedSolution,
            'adjustHref' => route('templates', $pricingPackageCatalog->selectionQuery($selection)),
            'contactHref' => $resolvedSolution['href'] ?? route('contact'),
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Plan>
     */
    private function plans()
    {
        return Plan::query()
            ->active()
            ->ordered()
            ->with('featureItems')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $selection
     */
    public static function persistSolution(User $user, array $selection): CustomerSolution
    {
        return tap($user->pricingSolution()->updateOrCreate(
            [],
            [
                'plan_id' => $selection['plan_id'] ?? null,
                'package_key' => $selection['package_key'],
                'locations' => $selection['locations'],
                'staff' => $selection['staff'],
                'bookings' => $selection['bookings'],
                'sections' => $selection['sections'],
                'package_options' => $selection['package_options'] ?? [],
                'source' => 'pricing_calculator',
            ],
        ))->loadMissing('plan');
    }
}
