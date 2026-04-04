<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\SiteFooterSetting;
use App\Models\SiteHeaderSetting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialThemePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_editorial_theme_site_can_be_rendered(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Editorial Tenant',
            'slug' => 'editorial-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Editorial Demo',
            'slug' => 'editorial-demo',
            'theme' => 'editorial',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Editorial Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $homePage->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $area->syncData([
            'title' => 'Editorial theme live',
            'copy' => 'Det her beviser at et andet theme kan rendere de samme indholdsomraadetyper.',
        ]);

        $response = $this->get('/sites/editorial-demo');

        $response->assertOk();
        $response->assertSee('Editorial theme live');
        $response->assertSee('Editorial Demo');
        $response->assertSee('site-theme--editorial', false);
    }

    public function test_editorial_theme_can_hide_brand_name_and_tagline_in_header(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Editorial Hidden Tenant',
            'slug' => 'editorial-hidden-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Editorial Hidden Demo',
            'slug' => 'editorial-hidden-demo',
            'theme' => 'editorial',
            'status' => 'ready',
            'is_online' => true,
        ]);

        SiteHeaderSetting::query()->create([
            'site_id' => $site->id,
            'brand_name' => 'Skjult Brand',
            'show_brand_name' => false,
            'tagline' => 'Skjult Tagline',
            'show_tagline' => false,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Editorial Hidden Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $homePage->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $area->syncData([
            'title' => 'Skjult header test',
            'copy' => 'Vi tester at brandnavn og tagline kan skjules helt.',
        ]);

        $response = $this->get('/sites/editorial-hidden-demo');

        $response->assertOk();
        $response->assertDontSee('Skjult Brand');
        $response->assertDontSee('Skjult Tagline');
    }

    public function test_editorial_theme_can_render_shared_footer_link_groups(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Editorial Footer Tenant',
            'slug' => 'editorial-footer-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Editorial Footer Demo',
            'slug' => 'editorial-footer-demo',
            'theme' => 'editorial',
            'status' => 'ready',
            'is_online' => true,
        ]);

        SiteFooterSetting::query()->create([
            'site_id' => $site->id,
            'information_links' => [
                ['label' => 'Privatlivspolitik', 'href' => '/privatliv'],
            ],
            'social_links' => [
                'instagram' => ['enabled' => true, 'href' => 'https://instagram.com/editorialdemo'],
            ],
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Editorial Footer Demo',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $homePage->areas()->create([
            'key' => 'hero-main',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $area->syncData([
            'title' => 'Footer title test',
            'copy' => 'Vi tester at editorial-footeren kan bruge en redigerbar overskrift.',
        ]);

        $response = $this->get('/sites/editorial-footer-demo');

        $response->assertOk();
        $response->assertSee('site-common-footer', false);
        $response->assertSee('Privatlivspolitik');
        $response->assertSee('https://instagram.com/editorialdemo');
        $response->assertSee('aria-label="Instagram"', false);
    }
}
