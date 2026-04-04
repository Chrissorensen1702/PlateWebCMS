<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Support\Http\LocalRedirect;
use App\Support\Sites\SiteDraftManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SitePublicationController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        SiteDraftManager::publishSite($site);

        $redirectTo = LocalRedirect::sanitize($request->string('redirect_to')->toString());

        return redirect()
            ->to($redirectTo ?? route('cms.sites.show', $site))
            ->with('status', "Alle kladde-aendringer for '{$site->name}' er publiceret.");
    }
}
