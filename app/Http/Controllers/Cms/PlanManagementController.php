<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanManagementController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManagePlans(), 403);

        return view('cms.pages.plans.index');
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlans(), 403);

        return redirect()
            ->route('cms.plans.index')
            ->with('status', 'Pakke-admin er sat på pause, mens vi nulstiller det gamle system.');
    }

    public function update(Request $request, Plan $plan): RedirectResponse
    {
        abort_unless($request->user()?->canManagePlans(), 403);

        return redirect()
            ->route('cms.plans.index')
            ->with('status', "Pakken '{$plan->name}' er ikke blevet ændret, fordi pakke-admin er sat på pause.");
    }
}
