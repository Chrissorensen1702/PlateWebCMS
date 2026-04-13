<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Support\Http\LocalRedirect;
use App\Support\Sites\SiteColorPalettes;
use App\Support\Sites\SiteDraftManager;
use App\Support\Sites\SitePageTemplates;
use App\Support\Sites\SiteThemes;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        $sites = Site::query()
            ->visibleTo($request->user())
            ->with(['tenant.users', 'plan'])
            ->withCount('pages')
            ->orderBy('name')
            ->get();

        return view('cms.pages.sites.index', [
            'sites' => $sites,
        ]);
    }

    public function show(Request $request, Site $site): View
    {
        $this->authorize('view', $site);
        SiteDraftManager::ensureDraftsForSite($site);

        $site->load([
            'tenant.users',
            'plan',
            'domains',
            'headerSettings',
            'footerSettings',
            'newsletterSettings',
            'bookingSettings',
            'colorSettings',
            'draftPages' => fn ($query) => $query
                ->with(['areas', 'sourcePage'])
                ->ordered(),
        ]);

        $activePageId = $request->integer('page');
        $activePage = $site->draftPages->firstWhere('id', $activePageId) ?? $site->draftPages->first();

        return view('cms.pages.sites.show', [
            'site' => $site,
            'sitePages' => $site->draftPages,
            'activePage' => $activePage,
            'canUpdateSite' => $request->user()->can('update', $site),
            'availablePageTemplates' => SitePageTemplates::availableForTheme($site->theme),
            'globalSections' => $this->globalSections(),
        ]);
    }

    public function globalContent(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('view', $site);

        return redirect()->route('cms.sites.global.section', [$site, 'header']);
    }

    public function globalSection(Request $request, Site $site, string $section): View
    {
        $this->authorize('view', $site);
        SiteDraftManager::ensureDraftsForSite($site);

        $sections = $this->globalSections();
        abort_unless(array_key_exists($section, $sections), 404);

        $site->load([
            'tenant.users',
            'plan',
            'domains',
            'headerSettings',
            'footerSettings',
            'newsletterSettings',
            'bookingSettings',
            'colorSettings',
            'draftPages' => fn ($query) => $query->ordered(),
        ]);

        return view('cms.pages.sites.global', [
            'site' => $site,
            'sitePages' => $site->draftPages,
            'canUpdateSite' => $request->user()->can('update', $site),
            'availableThemes' => SiteThemes::all(),
            'availableColorPalettes' => SiteColorPalettes::all(),
            'globalSections' => $sections,
            'activeGlobalSection' => $section,
            'activeGlobalSectionDefinition' => $sections[$section],
        ]);
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $validated = $request->validateWithBag('updateSite', [
            'name' => ['required', 'string', 'max:255'],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $site->forceFill([
            'name' => trim($validated['name']),
        ])->save();

        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.show', $site))
            ->with('status', "Websitenavnet er opdateret til '{$site->name}'.");
    }

    public function updateTheme(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $validated = $request->validateWithBag('updateSiteTheme', [
            'theme' => ['required', 'string', 'in:'.implode(',', SiteThemes::keys())],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $definition = SiteThemes::definition($validated['theme']);

        $site->forceFill([
            'theme' => $validated['theme'],
        ])->save();

        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.global.section', [$site, 'theme']))
            ->with('status', "Theme er opdateret til '{$definition['label']}'.");
    }

    public function updateColors(Request $request, Site $site): RedirectResponse
    {
        $this->authorize('update', $site);

        $validated = $request->validateWithBag('updateSiteColors', [
            'palette_key' => ['required', 'string', 'in:'.implode(',', SiteColorPalettes::keys())],
            'redirect_to' => ['nullable', 'string'],
        ]);

        $settings = $site->colorSettings()->firstOrNew();
        $settings->fill([
            'palette_key' => $validated['palette_key'],
        ]);
        $site->colorSettings()->save($settings);

        $definition = SiteColorPalettes::definition($validated['palette_key']);
        $redirectTo = LocalRedirect::sanitize($validated['redirect_to'] ?? null);

        return redirect()
            ->to($redirectTo ?? route('cms.sites.global.section', [$site, 'colors']))
            ->with('status', "Farvevalg er opdateret til '{$definition['label']}'.");
    }

    /**
     * @return array<string, array{label: string, card_copy: string, eyebrow: string, title: string, copy: string, partial: string}>
     */
    private function globalSections(): array
    {
        return [
            'header' => [
                'label' => 'Header',
                'card_copy' => 'Styr logo, brandnavn, tagline og den globale CTA i website-headeren.',
                'eyebrow' => 'Header',
                'title' => 'Website-header',
                'copy' => 'Her styrer du den globale top af websitet, sa logo, brand og CTA er ens pa tvaers af alle sider.',
                'partial' => 'header',
            ],
            'footer' => [
                'label' => 'Footer',
                'card_copy' => 'Styr navigation, kontaktoplysninger og sociale links i den faelles footer.',
                'eyebrow' => 'Footer',
                'title' => 'Website-footer',
                'copy' => 'Her styrer du den faelles footer, sa navigation, kontakt og sociale links er samlet et sted pa tvaers af hele websitet.',
                'partial' => 'footer',
            ],
            'colors' => [
                'label' => 'Farvevalg',
                'card_copy' => 'Vælg en fast palette som themes bruger til knapper, containere og accentflader.',
                'eyebrow' => 'Farver og udtryk',
                'title' => 'Fælles visuelle valg',
                'copy' => 'Vælg en fast farvepalette for hele websitet. Themes bruger derefter farverne automatisk i knapper, containere og fremhævede sektioner.',
                'partial' => 'colors',
            ],
            'theme' => [
                'label' => 'Themevalg',
                'card_copy' => 'Skift det overordnede theme og hold styr på hvilket visuelt udtryk sitet bruger.',
                'eyebrow' => 'Themevalg',
                'title' => 'Website-theme',
                'copy' => 'Vælg det samlede visuelle udtryk for hele websitet. Themes er bevidst ret forskellige, så det giver mening at skifte mellem dem.',
                'partial' => 'theme',
            ],
            'domain' => [
                'label' => 'Domæne og DNS',
                'card_copy' => 'Se hvordan domæneopsætning og DNS skal hænge sammen for websitet.',
                'eyebrow' => 'Domæne',
                'title' => 'Site-domæner og publicering',
                'copy' => 'Når vi senere bygger selvbetjening til domæner, er det her den naturlige plads i CMS’et.',
                'partial' => 'domain',
            ],
            'plan' => [
                'label' => 'Ændre plan',
                'card_copy' => 'Hold styr på valgt websiteplan og den overordnede ramme for sitet.',
                'eyebrow' => 'Website plan',
                'title' => 'Plan og fælles ramme',
                'copy' => 'Her holder vi styr på den valgte websiteplan og den overordnede ramme, som gælder for hele sitet.',
                'partial' => 'plan',
            ],
            'booking' => [
                'label' => 'Bookingsystem',
                'card_copy' => 'Aktiver bookingsystemet, link til en eksisterende konto eller forbered en ny fra ét globalt modul.',
                'eyebrow' => 'Bookingsystem',
                'title' => 'Benyt bookingsystem',
                'copy' => 'Hold bookingsystemet som et separat driftssystem, men styr koblingen og hvordan booking bruges på hjemmesiden herfra.',
                'partial' => 'booking',
            ],
            'newsletter' => [
                'label' => 'Nyhedsbrev',
                'card_copy' => 'Styr nyhedsbrev-formular, tekst og hvor tilmeldinger skal samles fra et globalt modul.',
                'eyebrow' => 'Nyhedsbrev',
                'title' => 'Nyhedsbrev og tilmeldinger',
                'copy' => 'Her sætter vi den globale nyhedsbrev-oplevelse op, så kunden kan aktivere formularen og samle tilmeldinger uden at rode rundt på de enkelte sider.',
                'partial' => 'newsletter',
            ],
            'seo' => [
                'label' => 'SEO',
                'card_copy' => 'Administrer globale SEO-rammer og synlighed, uden at det blandes ind i de lokale sider.',
                'eyebrow' => 'SEO',
                'title' => 'Global synlighed',
                'copy' => 'Her kan vi samle globale SEO-indstillinger, så de ikke forsvinder rundt på de enkelte sider.',
                'partial' => 'seo',
            ],
        ];
    }
}
