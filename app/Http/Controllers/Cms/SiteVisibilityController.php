<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Support\Http\LocalRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SiteVisibilityController extends Controller
{
    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $validated = $request->validate([
            'is_online' => ['required', 'boolean'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $isOnline = (bool) $validated['is_online'];

        $site->forceFill([
            'is_online' => $isOnline,
            'launched_at' => $isOnline && $site->launched_at === null ? Carbon::now() : $site->launched_at,
        ])->save();

        $message = $isOnline
            ? "Websitet '{$site->name}' er nu online."
            : "Websitet '{$site->name}' er nu offline.";

        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.show', $site))
            ->with('status', $message);
    }
}
