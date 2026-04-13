<?php

namespace App\Http\Controllers\Sites;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Support\Sites\SiteThemes;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    /**
     * Render a customer site preview using the selected theme.
     */
    public function show(Request $request, Site $site, ?string $pageSlug = null): View
    {
        $site->loadMissing(['headerSettings', 'footerSettings', 'colorSettings', 'bookingSettings', 'tenant']);

        if (! $site->is_online) {
            $viewer = $request->user();

            abort_if($viewer === null || ! $viewer->can('view', $site), 404);
        }

        $navigation = $site->pages()
            ->published()
            ->ordered()
            ->get();

        $pageQuery = $site->pages()
            ->published()
            ->with([
                'areas' => fn ($query) => $query->active()->ordered(),
            ]);

        $page = $pageSlug
            ? (clone $pageQuery)->where('slug', $pageSlug)->firstOrFail()
            : (clone $pageQuery)->home()->first() ?? (clone $pageQuery)->ordered()->firstOrFail();

        $theme = $this->resolveTheme($request, $site);

        return view("sites.themes.{$theme}.page", [
            'site' => $site,
            'page' => $page,
            'navigation' => $navigation,
            'theme' => $theme,
        ]);
    }

    private function resolveTheme(Request $request, Site $site): string
    {
        $previewTheme = (string) $request->query('preview_theme', '');

        if ($previewTheme !== '') {
            $viewer = $request->user();

            if ($viewer !== null && $viewer->can('view', $site) && in_array($previewTheme, SiteThemes::keys(), true)) {
                return $previewTheme;
            }
        }

        $theme = $site->theme ?: 'base';

        return view()->exists("sites.themes.{$theme}.page")
            ? $theme
            : 'base';
    }
}
