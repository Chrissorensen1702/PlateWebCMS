<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Support\Sites\SiteThemes;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerManagementController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user?->isDeveloper(), 403);

        $tenants = Tenant::query()
            ->with([
                'users',
                'sites' => fn ($query) => $query->with('plan')->latest('updated_at'),
            ])
            ->withCount('sites')
            ->latest()
            ->get();

        return view('cms.pages.customers.index', [
            'tenants' => $tenants,
            'plans' => Plan::query()->active()->ordered()->get(),
            'availableThemes' => SiteThemes::all(),
            'canCreateCustomerSites' => $user->canManageCustomers(),
        ]);
    }
}
