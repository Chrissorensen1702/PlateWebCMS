<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Site;
use App\Models\SitePage;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Sites\SiteDraftManager;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'owner@example.com',
        ], [
            'name' => 'Chris Sorensen',
            'password' => 'password',
            'role' => 'developer',
            'email_verified_at' => now(),
        ]);

        $client = User::query()->updateOrCreate([
            'email' => 'client@example.com',
        ], [
            'name' => 'Mia Jensen',
            'password' => 'password',
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        $viewer = User::query()->updateOrCreate([
            'email' => 'viewer@example.com',
        ], [
            'name' => 'Jonas Nielsen',
            'password' => 'password',
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        $tenant = Tenant::query()->firstWhere('slug', 'north-studio')
            ?? $client->tenants()->first()
            ?? new Tenant();

        $tenant->fill([
            'name' => 'North Studio',
            'company_email' => 'hello@northstudio.dk',
            'billing_email' => 'faktura@northstudio.dk',
            'phone' => '+45 12 34 56 78',
            'cvr_number' => '12345678',
            'website_url' => 'https://northstudio.dk',
            'slug' => 'north-studio',
            'status' => 'active',
            'notes' => 'Demo-tenant til at teste kundelogin og site-adgang.',
        ]);
        $tenant->save();

        $client->tenants()->sync([
            $tenant->id => ['role' => 'owner'],
        ]);

        $viewer->tenants()->sync([
            $tenant->id => ['role' => 'viewer'],
        ]);

        $plans = [
            [
                'name' => 'Template Start',
                'slug' => 'template-start',
                'kind' => 'template',
                'headline' => 'Hurtig launch med et staerkt udgangspunkt',
                'summary' => 'Et designet templatesite med CMS til tekst, billeder og kontaktoplysninger.',
                'price_from' => 7900,
                'build_time' => '1-2 uger',
                'sort_order' => 1,
                'features' => [
                    'Professionel template',
                    'Op til 5 sektioner',
                    'Kundelogin til indhold',
                    'Mobiloptimeret opsaetning',
                ],
            ],
            [
                'name' => 'Template Pro',
                'slug' => 'template-pro',
                'kind' => 'template',
                'headline' => 'Mere branding, flere sider og et skarpere udtryk',
                'summary' => 'Til kunder der vil have mere fleksibilitet, flere sektioner og en mere premium launch.',
                'price_from' => 14900,
                'build_time' => '2-3 uger',
                'sort_order' => 2,
                'features' => [
                    'Udvidet templatesite',
                    'Flere indholdssektioner',
                    'Mere visuel tilpasning',
                    'Kundelogin og billedhaandtering',
                ],
            ],
            [
                'name' => 'Custom Build',
                'slug' => 'custom-build',
                'kind' => 'custom',
                'headline' => 'Specialbygget hjemmeside med samme CMS-fundament',
                'summary' => 'Et unikt site bygget fra bunden, stadig koblet til dit eget kontrollerede kundelogin.',
                'price_from' => null,
                'build_time' => 'Efter scope',
                'sort_order' => 3,
                'features' => [
                    'Skraeddersyet design',
                    'Udvalgte redigerbare sektioner',
                    'Mulighed for specialfunktioner',
                    'Samme CMS-login som templates',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = Plan::query()->updateOrCreate([
                'slug' => $planData['slug'],
            ], $planData);

            $plan->syncFeatures($features);
        }

        $templatePlan = Plan::query()->firstWhere('slug', 'template-start');

        $site = Site::query()->updateOrCreate([
            'slug' => 'north-studio-demo',
        ], [
            'plan_id' => $templatePlan?->id,
            'tenant_id' => $tenant->id,
            'name' => 'North Studio Demo',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
            'notes' => 'Demo-site til at teste pages, indholdsomraader og theme-strukturen.',
        ]);

        $homePage = SitePage::query()->updateOrCreate([
            'site_id' => $site->id,
            'slug' => 'home',
        ], [
            'name' => 'Forside',
            'title' => 'North Studio Demo',
            'meta_description' => 'Et eksempel paa et kundesite bygget i den nye sites-struktur.',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $aboutPage = SitePage::query()->updateOrCreate([
            'site_id' => $site->id,
            'slug' => 'om-os',
        ], [
            'name' => 'Om os',
            'title' => 'Om North Studio Demo',
            'meta_description' => 'Ekstra underside i demo-sitet.',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->upsertArea($homePage, 'hero-main', 'hero', 'Topsektion', 1, [
            'eyebrow' => 'Template Demo',
            'title' => 'Et kundesite kan nu rendere fra samme platform.',
            'copy' => 'Pages og indholdsomraader ligger i databasen, mens dette tema viser hvordan vi kan holde kundevisningen adskilt fra salgssiden og CMS-et.',
            'primary_cta_label' => 'Se kontaktsiden',
            'primary_cta_href' => route('contact', [], false),
            'secondary_cta_label' => 'Laes om kunde-CMS',
            'secondary_cta_href' => route('sales.customer-cms', [], false),
        ]);

        $this->upsertArea($homePage, 'intro-content', 'content', 'Struktur', 2, [
            'eyebrow' => 'Struktur',
            'title' => 'Denne side viser den nye retning for kundesites.',
            'copy' => 'Hvert site kan have sit eget theme, sine egne pages og kontrollerede indholdsomraader. Det giver os en stabil vej videre til et rigtigt CMS-flow.',
            'items' => [
                'Sites har nu deres eget theme-felt.',
                'Pages holder styr paa navigation og public URL-er.',
                'Sideafsnit holder indholdet fleksibelt uden at layoutet flyder ud.',
            ],
        ]);

        $this->upsertArea($homePage, 'contact-block', 'contact', 'Naeste lag', 3, [
            'eyebrow' => 'Naeste lag',
            'title' => 'Herfra kan vi bygge editoren.',
            'copy' => 'Naeste naturlige skridt er at lade developer eller kunde oprette og redigere pages og indholdsomraader inde fra CMS-et.',
            'email' => 'owner@example.com',
            'phone' => '+45 12 34 56 78',
            'cta_label' => 'Tilbage til salgssiden',
            'cta_href' => route('home', [], false),
        ]);

        $this->upsertArea($aboutPage, 'about-content', 'content', 'Om siden', 1, [
            'eyebrow' => 'Om siden',
            'title' => 'En ekstra underside viser page-strukturen.',
            'copy' => 'Du kan nu have flere public pages pr. site. Senere kan vi koble en rigtig page-editor paa, uden at aendre den overordnede arkitektur.',
            'items' => [
                'Preview-ruter virker allerede for sites.',
                'Theme-filerne ligger samlet under resources/views/sites.',
                'Samme data-model kan bruges til template-kunder og custom builds.',
            ],
        ]);

        $editorialSite = Site::query()->updateOrCreate([
            'slug' => 'north-studio-editorial',
        ], [
            'plan_id' => Plan::query()->firstWhere('slug', 'template-pro')?->id,
            'tenant_id' => $tenant->id,
            'name' => 'Maison Glow',
            'theme' => 'editorial',
            'status' => 'ready',
            'is_online' => true,
            'notes' => 'Demo-site der viser editorial-theme som en feminin skoenhedssalon med bookingfokus.',
        ]);

        $editorialHomePage = SitePage::query()->updateOrCreate([
            'site_id' => $editorialSite->id,
            'slug' => 'home',
        ], [
            'name' => 'Forside',
            'title' => 'Maison Glow | Skoenhedssalon med rolige behandlinger',
            'meta_description' => 'Maison Glow er en feminin skoenhedssalon med fokus paa hudpleje, ro og personlige behandlinger.',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $legacyEditorialPage = SitePage::query()
            ->where('site_id', $editorialSite->id)
            ->where('slug', 'retning')
            ->first();

        if ($legacyEditorialPage !== null && ! SitePage::query()->where('site_id', $editorialSite->id)->where('slug', 'behandlinger')->exists()) {
            $legacyEditorialPage->update([
                'slug' => 'behandlinger',
                'name' => 'Behandlinger',
                'title' => 'Behandlinger hos Maison Glow',
                'meta_description' => 'Overblik over behandlinger hos Maison Glow.',
            ]);
        }

        SitePage::query()
            ->where('site_id', $editorialSite->id)
            ->where('slug', 'retning')
            ->delete();

        $editorialTreatmentsPage = SitePage::query()->updateOrCreate([
            'site_id' => $editorialSite->id,
            'slug' => 'behandlinger',
        ], [
            'name' => 'Behandlinger',
            'title' => 'Behandlinger hos Maison Glow',
            'meta_description' => 'Se signaturbehandlinger, behandlingstrin og booking hos Maison Glow.',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->upsertArea($editorialHomePage, 'hero-main', 'hero', 'Topsektion', 1, [
            'eyebrow' => 'Skoenhedssalon i rolige rammer',
            'title' => 'Maison Glow skaber ro, glow og tid til dig.',
            'copy' => 'Brug editorial-themeet til en feminin salonoplevelse med blide farver, tydelig bookingvej og plads til at fortaelle om behandlingerne.',
            'image_url' => '/images/demo/maison-glow-hero.svg',
            'image_alt' => 'Stemningsbillede til Maison Glow med roligt salonudtryk',
            'primary_cta_label' => 'Book tid',
            'primary_cta_href' => route('sites.show', ['site' => 'north-studio-editorial'], false).'#contact-block',
            'secondary_cta_label' => 'Se behandlinger',
            'secondary_cta_href' => route('sites.page', ['site' => 'north-studio-editorial', 'pageSlug' => 'behandlinger'], false),
        ]);

        $this->upsertArea($editorialHomePage, 'story-content', 'content', 'Signaturbehandlinger', 2, [
            'eyebrow' => 'Populaere behandlinger',
            'title' => 'Tre rolige behandlinger, som passer perfekt til themeet.',
            'copy' => 'Afsnittet fungerer godt til salonens mest efterspurgte behandlinger og giver hurtigt besoegende et overblik.',
            'items' => [
                'Glow Facial med fokus paa fugt, glans og afslapning.',
                'Bryn og vipper med naturligt, roligt udtryk.',
                'Mini ritualer til travle kunder, der stadig vil forkæles.',
            ],
        ]);

        $this->upsertArea($editorialHomePage, 'contact-block', 'contact', 'Book tid', 3, [
            'eyebrow' => 'Booking',
            'title' => 'Book din tid eller skriv for en personlig anbefaling.',
            'copy' => 'Den afsluttende sektion samler booking, kontakt og en rolig invitation til at tage det naeste skridt.',
            'email' => 'book@maison-glow.dk',
            'phone' => '+45 31 22 44 55',
            'cta_label' => 'Book behandling',
            'cta_href' => 'mailto:book@maison-glow.dk',
        ]);

        $this->upsertArea($editorialTreatmentsPage, 'treatments-hero', 'hero', 'Intro', 1, [
            'eyebrow' => 'Behandlinger',
            'title' => 'Behandlinger med fokus paa hud, ro og et naturligt glow.',
            'copy' => 'Brug siden til at forklare behandlingstyper, forventninger og hvad der er inkluderet, uden at miste det rolige salonudtryk.',
            'image_url' => '/images/demo/maison-glow-hero.svg',
            'image_alt' => 'Maison Glow behandlinger og salonmiljoe',
            'primary_cta_label' => 'Book tid',
            'primary_cta_href' => route('sites.show', ['site' => 'north-studio-editorial'], false).'#contact-block',
            'secondary_cta_label' => 'Tilbage til forsiden',
            'secondary_cta_href' => route('sites.show', ['site' => 'north-studio-editorial'], false),
        ]);

        $this->upsertArea($editorialTreatmentsPage, 'direction-content', 'content', 'Behandlinger', 2, [
            'eyebrow' => 'Menu',
            'title' => 'Vaelg den behandling der passer til din hud og din kalender.',
            'copy' => 'Et tekstafsnit som dette fungerer godt til behandlingsoversigt, varighed og den vaerdi kunden faar med hjem.',
            'items' => [
                'Glow Facial - 60 min. med dybderens, massage og afsluttende pleje.',
                'Skin Reset - 45 min. til traet eller sensitiv hud, der mangler balance.',
                'Bryn & vipper - finish og definition med et naturligt udtryk.',
            ],
        ]);

        $this->upsertArea($editorialTreatmentsPage, 'treatments-booking', 'contact', 'Booking', 3, [
            'eyebrow' => 'Klar til booking',
            'title' => 'Skriv til os, hvis du er i tvivl om hvilken behandling du skal vaelge.',
            'copy' => 'Sektionen er god til FAQ-light, kontaktinfo og en tryg sidste handling.',
            'email' => 'book@maison-glow.dk',
            'phone' => '+45 31 22 44 55',
            'cta_label' => 'Send bookingforespoergsel',
            'cta_href' => 'mailto:book@maison-glow.dk',
        ]);

        SiteDraftManager::refreshDraftsFromLive($site);
        SiteDraftManager::refreshDraftsFromLive($editorialSite);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function upsertArea(SitePage $page, string $key, string $type, string $label, int $sortOrder, array $data): void
    {
        $area = $page->areas()->updateOrCreate([
            'area_key' => $key,
        ], [
            'area_type' => $type,
            'label' => $label,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        $area->syncData($data);
    }
}
