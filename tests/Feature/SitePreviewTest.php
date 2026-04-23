<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\SiteColorSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_site_home_page_can_be_rendered(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Site',
            'slug' => 'demo-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Demo Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $area->syncData([
            'title' => 'Velkommen til demo-sitet',
            'copy' => 'Dette viser, at kundesider nu kan rendere fra sites-omraadet.',
        ]);

        $response = $this->get('/sites/demo-site');

        $response->assertOk();
        $response->assertSee('Velkommen til demo-sitet');
        $response->assertSee('Demo Site');
    }

    public function test_customer_site_sub_page_can_be_rendered(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Site',
            'slug' => 'demo-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Demo Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $aboutPage = $site->pages()->create([
            'name' => 'Om os',
            'slug' => 'om-os',
            'title' => 'Om demo-sitet',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $area = $aboutPage->areas()->create([
            'key' => 'intro',
            'type' => 'content',
            'sort_order' => 1,
        ]);

        $area->syncData([
            'title' => 'Ekstra underside',
            'copy' => 'Denne underside viser page-routing for kundesider.',
        ]);

        $response = $this->get('/sites/demo-site/om-os');

        $response->assertOk();
        $response->assertSee('Ekstra underside');
        $response->assertSee('Om os');
    }

    public function test_customer_site_can_render_a_published_custom_layout_page(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Custom Tenant',
            'slug' => 'custom-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Custom Site',
            'slug' => 'custom-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Custom Site Forside',
            'layout_mode' => 'custom-main',
            'custom_html' => '<section class="custom-stage"><h1>Bygget i custom mode</h1></section>',
            'custom_css' => '.custom-stage{padding:4rem 0;}',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/sites/custom-site');

        $response->assertOk();
        $response->assertSee('Bygget i custom mode', false);
        $response->assertSee('.custom-stage{padding:4rem 0;}', false);
    }

    public function test_customer_site_can_render_a_full_custom_layout_page_without_theme_chrome(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Full Custom Tenant',
            'slug' => 'full-custom-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Full Custom Site',
            'slug' => 'full-custom-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Full Custom Site Forside',
            'layout_mode' => 'custom-full',
            'custom_html' => '<section class="custom-full-stage"><h1>Hele siden er custom</h1></section>',
            'custom_css' => '.custom-full-stage{min-height:100vh;}',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get('/sites/full-custom-site');

        $response->assertOk();
        $response->assertSee('Hele siden er custom', false);
        $response->assertDontSee('site-theme-header', false);
        $response->assertDontSee('site-common-footer', false);
    }

    public function test_base_theme_hero_can_render_image_in_split_layout(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Base Image Tenant',
            'slug' => 'base-image-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Base Image Site',
            'slug' => 'base-image-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Base Image Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Hero med billede',
            'copy' => 'Base-themeet skal kunne vise et billede i højre side af heroen.',
            'image_url' => '/images/demo/maison-glow-hero.svg',
            'image_alt' => 'Salon hero billede',
            'image_focus' => 'right',
            'primary_cta_label' => 'Kontakt',
            'primary_cta_href' => '/kontakt',
        ]);

        $response = $this->get('/sites/base-image-site');

        $response->assertOk();
        $response->assertSee('Hero med billede');
        $response->assertSee('Salon hero billede');
        $response->assertSee('site-hero--with-media', false);
        $response->assertSee('object-position: right center;', false);
    }

    public function test_base_theme_contact_can_render_google_map_layout(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Map Tenant',
            'slug' => 'map-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Map Site',
            'slug' => 'map-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Map Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $contact = $homePage->areas()->create([
            'key' => 'contact',
            'type' => 'contact',
            'sort_order' => 1,
        ]);

        $contact->syncData([
            'layout_style' => 'map',
            'copy' => 'Find os midt i byen med nem parkering tæt på.',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2250.123456789!2d9.123456!3d56.123456!2m3!1f0!2f0!3f0',
        ]);

        $response = $this->get('/sites/map-site');

        $response->assertOk();
        $response->assertSee('Her finder du os');
        $response->assertSee('site-contact--split', false);
        $response->assertSee('site-contact__map-frame', false);
        $response->assertSee('https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2250.123456789!2d9.123456!3d56.123456!2m3!1f0!2f0!3f0', false);
    }

    public function test_base_theme_services_page_can_render_product_and_price_columns(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Services Tenant',
            'slug' => 'services-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Services Site',
            'slug' => 'services-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Services Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $servicesPage = $site->pages()->create([
            'name' => 'Behandlinger',
            'slug' => 'behandlinger',
            'title' => 'Vores behandlinger',
            'template_key' => 'services',
            'is_home' => false,
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $servicesList = $servicesPage->areas()->create([
            'key' => 'services-list',
            'type' => 'content',
            'sort_order' => 1,
        ]);

        $servicesList->syncData([
            'eyebrow' => 'Overblik',
            'title' => 'Vores ydelser',
            'copy' => 'Et hurtigt overblik over de vigtigste behandlinger.',
            'items' => [
                'Ansigtsbehandling',
                'Bryn og vipper',
            ],
            'service_prices' => [
                'Fra 499 kr.',
                'Fra 299 kr.',
            ],
        ]);

        $response = $this->get('/sites/services-site/behandlinger');

        $response->assertOk();
        $response->assertSee('site-panel--services-catalog', false);
        $response->assertSee('Produkt');
        $response->assertSee('Pris');
        $response->assertSee('Ansigtsbehandling');
        $response->assertSee('Fra 499 kr.');
    }

    public function test_site_uses_selected_global_color_palette(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Palette Tenant',
            'slug' => 'palette-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Palette Demo',
            'slug' => 'palette-demo',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        SiteColorSetting::query()->create([
            'site_id' => $site->id,
            'palette_key' => 'forest',
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Palette Demo Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Palette Demo',
            'copy' => 'Globalt farvevalg skal slå igennem på det offentlige site.',
        ]);

        $response = $this->get('/sites/palette-demo');

        $response->assertOk();
        $response->assertSee('--color-primary: #2f6f5a', false);
        $response->assertSee('--color-accent: #d9a441', false);
    }


    public function test_base_theme_renders_its_fixed_footer(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Footer Variant Tenant',
            'slug' => 'footer-variant-tenant',
            'status' => 'active',
            'company_email' => 'hello@example.test',
            'phone' => '+45 12 34 56 78',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Footer Variant Demo',
            'slug' => 'footer-variant-demo',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->headerSettings()->create([
            'show_cta' => true,
            'cta_label' => 'Book en samtale',
            'cta_href' => '/kontakt',
        ]);

        $site->footerSettings()->create([
            'navigation_links' => [
                ['label' => 'Forside', 'href' => '/sites/footer-variant-demo'],
                ['label' => 'Behandlinger', 'href' => '/behandlinger'],
            ],
            'information_links' => [
                ['label' => 'Privatlivspolitik', 'href' => '/privatliv'],
            ],
            'social_links' => [
                'instagram' => ['enabled' => true, 'href' => 'https://instagram.com/footerdemo'],
            ],
            'contact_email' => 'footer@example.test',
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Footer Variant Demo Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Footer Variant Demo',
            'copy' => 'Det faste footerdesign skal slå igennem på det offentlige site.',
        ]);

        $response = $this->get('/sites/footer-variant-demo');

        $response->assertOk();
        $response->assertSee('site-common-footer', false);
        $response->assertSee('Behandlinger');
        $response->assertSee('Privatlivspolitik');
        $response->assertSee('footer@example.test');
        $response->assertSee('https://instagram.com/footerdemo');
        $response->assertSee('aria-label="Instagram"', false);
        $response->assertSee('Alle rettigheder reserveret');
        $response->assertSee('PlateWeb.dk');
        $response->assertSee('CVR: 42456187');
    }

    public function test_public_preview_filters_existing_unsafe_links_before_rendering(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Unsafe Preview Tenant',
            'slug' => 'unsafe-preview-tenant',
            'status' => 'active',
            'company_email' => 'unsafe@example.test',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Unsafe Preview Demo',
            'slug' => 'unsafe-preview-demo',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->headerSettings()->create([
            'show_cta' => true,
            'cta_label' => 'Book nu',
            'cta_href' => 'javascript:alert(1)',
        ]);

        $site->footerSettings()->create([
            'navigation_links' => [
                ['label' => 'Ond navigation', 'href' => 'javascript:alert(2)'],
            ],
            'information_links' => [
                ['label' => 'Ond info', 'href' => 'data:text/html;base64,SGVq'],
            ],
            'social_links' => [
                'instagram' => ['enabled' => true, 'href' => 'javascript:alert(3)'],
            ],
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Unsafe Preview Demo Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Unsafe Preview Demo',
            'copy' => 'Usikre links skal filtreres væk i renderingen.',
            'primary_cta_label' => 'Ond hero CTA',
            'primary_cta_href' => 'javascript:alert(4)',
        ]);

        $contact = $homePage->areas()->create([
            'key' => 'contact',
            'type' => 'contact',
            'sort_order' => 2,
        ]);

        $contact->syncData([
            'title' => 'Kontakt',
            'cta_label' => 'Ond kontakt CTA',
            'cta_href' => 'javascript:alert(5)',
        ]);

        $response = $this->get('/sites/unsafe-preview-demo');

        $response->assertOk();
        $response->assertDontSee('javascript:alert', false);
        $response->assertDontSee('data:text/html', false);
        $response->assertDontSee('Ond navigation');
        $response->assertDontSee('Ond info');
        $response->assertDontSee('aria-label="Instagram"', false);
    }

    public function test_booking_settings_can_drive_header_cta_on_public_site(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Booking Header Tenant',
            'slug' => 'booking-header-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Booking Header Site',
            'slug' => 'booking-header-site',
            'theme' => 'minimal',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->bookingSettings()->create([
            'is_enabled' => true,
            'connection_mode' => 'existing',
            'booking_url' => 'https://booking.example.test/header-site',
            'cta_label' => 'Book tid nu',
            'use_on_website' => true,
            'show_in_header' => true,
            'open_in_new_tab' => true,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Booking Header Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Booking Header Demo',
            'copy' => 'Global booking-CTA skal kunne drive headeren.',
        ]);

        $response = $this->get('/sites/booking-header-site');

        $response->assertOk();
        $response->assertSee('Book tid nu');
        $response->assertSee('https://booking.example.test/header-site');
        $response->assertSee('target="_blank"', false);
    }

    public function test_header_appearance_settings_are_reflected_on_public_site(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Header Appearance Tenant',
            'slug' => 'header-appearance-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Header Appearance Site',
            'slug' => 'header-appearance-site',
            'theme' => 'minimal',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->headerSettings()->create([
            'show_brand_name' => true,
            'show_cta' => true,
            'cta_label' => 'Kontakt os',
            'cta_href' => '/contact',
            'background_style' => 'dark',
            'text_color_style' => 'light',
            'shadow_style' => 'strong',
            'sticky_mode' => 'static',
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Header Appearance Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Header Appearance Demo',
            'copy' => 'Den globale header skal kunne tage baggrund, skygge og sticky-valg med ud pa sitet.',
        ]);

        $response = $this->get('/sites/header-appearance-site');

        $response->assertOk();
        $response->assertSee('site-theme-header--bg-dark', false);
        $response->assertSee('site-theme-header--text-light', false);
        $response->assertSee('site-theme-header--shadow-strong', false);
        $response->assertSee('site-theme-header--mode-static', false);
    }

    public function test_base_contact_section_can_fall_back_to_global_booking_cta(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Booking Contact Tenant',
            'slug' => 'booking-contact-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Booking Contact Site',
            'slug' => 'booking-contact-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->bookingSettings()->create([
            'is_enabled' => true,
            'connection_mode' => 'existing',
            'booking_url' => 'https://booking.example.test/contact-site',
            'cta_label' => 'Book din tid',
            'use_on_website' => true,
            'show_in_contact_sections' => true,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Booking Contact Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $contact = $homePage->areas()->create([
            'key' => 'contact',
            'type' => 'contact',
            'sort_order' => 1,
        ]);

        $contact->syncData([
            'title' => 'Kontakt os',
            'copy' => 'Den globale bookingknap skal kunne falde tilbage her.',
        ]);

        $response = $this->get('/sites/booking-contact-site');

        $response->assertOk();
        $response->assertSee('Book din tid');
        $response->assertSee('https://booking.example.test/contact-site');
    }

    public function test_footer_can_hide_fallback_contact_fields(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Footer Hidden Tenant',
            'slug' => 'footer-hidden-tenant',
            'status' => 'active',
            'company_email' => 'hidden@example.test',
            'phone' => '+45 99 88 77 66',
            'cvr_number' => '99887766',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Footer Hidden Demo',
            'slug' => 'footer-hidden-demo',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->footerSettings()->create([
            'show_contact_email' => false,
            'show_contact_phone' => false,
            'show_contact_cvr' => false,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Footer Hidden Demo Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Footer Hidden Demo',
            'copy' => 'Skjulte footerfelter skal ikke falde tilbage til tenant-data.',
        ]);

        $response = $this->get('/sites/footer-hidden-demo');

        $response->assertOk();
        $response->assertDontSee('hidden@example.test');
        $response->assertDontSee('+45 99 88 77 66');
        $response->assertDontSee('99887766');
    }


    public function test_logged_in_tenant_user_can_preview_an_alternate_theme_with_query_parameter(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Preview Theme Tenant',
            'slug' => 'preview-theme-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Preview Theme Site',
            'slug' => 'preview-theme-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Preview Theme Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $hero = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $hero->syncData([
            'title' => 'Preview Theme Site',
            'copy' => 'Et logged-in preview skal kunne vise et alternativt theme uden at gemme det.',
        ]);

        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $user->tenants()->attach($tenant->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->get('/sites/preview-theme-site?preview_theme=midnight');

        $response->assertOk();
        $response->assertSee('site-theme--midnight', false);
    }

    public function test_unknown_customer_site_page_returns_not_found(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Site',
            'slug' => 'demo-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => true,
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Demo Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/sites/demo-site/findes-ikke')->assertNotFound();
    }

    public function test_offline_customer_site_returns_not_found_for_guests(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Site',
            'slug' => 'demo-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => false,
        ]);

        $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Demo Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->get('/sites/demo-site')->assertNotFound();
    }

    public function test_offline_customer_site_can_still_be_previewed_by_logged_in_tenant_user(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Demo Tenant',
            'slug' => 'demo-tenant',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Demo Site',
            'slug' => 'demo-site',
            'theme' => 'base',
            'status' => 'ready',
            'is_online' => false,
        ]);

        $homePage = $site->pages()->create([
            'name' => 'Forside',
            'slug' => 'home',
            'title' => 'Demo Site Forside',
            'is_home' => true,
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $area = $homePage->areas()->create([
            'key' => 'hero',
            'type' => 'hero',
            'sort_order' => 1,
        ]);

        $area->syncData([
            'title' => 'Velkommen til demo-sitet',
            'copy' => 'Dette viser, at kundesider nu kan rendere fra sites-omraadet.',
        ]);

        $user = \App\Models\User::factory()->create([
            'role' => 'client',
        ]);

        $user->tenants()->attach($tenant->id, ['role' => 'owner']);

        $response = $this->actingAs($user)->get('/sites/demo-site');

        $response->assertOk();
        $response->assertSee('Velkommen til demo-sitet');
    }

    public function test_new_customer_themes_can_be_rendered(): void
    {
        foreach (['minimal', 'midnight', 'spotlight'] as $theme) {
            $tenant = Tenant::query()->create([
                'name' => ucfirst($theme).' Tenant',
                'slug' => $theme.'-tenant',
                'status' => 'active',
            ]);

            $site = Site::query()->create([
                'tenant_id' => $tenant->id,
                'name' => ucfirst($theme).' Site',
                'slug' => $theme.'-site',
                'theme' => $theme,
                'status' => 'ready',
                'is_online' => true,
            ]);

            $homePage = $site->pages()->create([
                'name' => 'Forside',
                'slug' => 'home',
                'title' => ucfirst($theme).' Site Forside',
                'is_home' => true,
                'is_published' => true,
                'sort_order' => 1,
            ]);

            $hero = $homePage->areas()->create([
                'key' => 'hero',
                'type' => 'hero',
                'sort_order' => 1,
            ]);

            $hero->syncData([
                'eyebrow' => strtoupper($theme),
                'title' => 'Preview for '.$theme,
                'copy' => 'Det her viser at themeet rendrer korrekt med de fælles area-typer.',
                'primary_cta_label' => 'Kontakt',
                'primary_cta_href' => '/kontakt',
            ]);

            $content = $homePage->areas()->create([
                'key' => 'content',
                'type' => 'content',
                'sort_order' => 2,
            ]);

            $content->syncData([
                'title' => 'Forskelligt udtryk',
                'copy' => 'Samme data kan få en helt anden visuel form.',
                'items_style' => 'cards',
                'items' => [
                    'Første kort',
                    'Andet kort',
                    'Tredje kort',
                ],
            ]);

            $stats = $homePage->areas()->create([
                'key' => 'stats',
                'type' => 'stats',
                'sort_order' => 3,
            ]);

            $stats->syncData([
                'title' => 'Tal der bygger tillid',
                'copy' => 'Forskellige themes skal stadig kunne vise fælles nøgletal.',
                'display_style' => 'cards',
                'items' => [
                    '98% | Tilfredse kunder',
                    '24 timer | Typisk svartid',
                ],
            ]);

            $quote = $homePage->areas()->create([
                'key' => 'quote',
                'type' => 'quote',
                'sort_order' => 4,
            ]);

            $quote->syncData([
                'eyebrow' => 'Udtalelse',
                'quote_text' => 'Det er samme indholdslag, men themes kan stadig føles helt forskellige.',
                'quote_author' => 'Preview Kunde',
                'quote_role' => 'Testperson',
                'text_align' => 'center',
            ]);

            $faq = $homePage->areas()->create([
                'key' => 'faq',
                'type' => 'faq',
                'sort_order' => 5,
            ]);

            $faq->syncData([
                'title' => 'Spørgsmål og svar',
                'copy' => 'FAQ skal også kunne falde tilbage til shared area-visninger.',
                'layout_style' => 'cards',
                'items' => [
                    'Hvordan virker det? | Themeet renderer shared areas.',
                    'Kan de styles forskelligt? | Ja, hvert theme giver sit eget udtryk.',
                ],
            ]);

            $response = $this->get("/sites/{$site->slug}");

            $response->assertOk();
            $response->assertSee('Preview for '.$theme);
            $response->assertSee('Tal der bygger tillid');
            $response->assertSee('Det er samme indholdslag, men themes kan stadig føles helt forskellige.');
            $response->assertSee('Hvordan virker det?');
            $response->assertSee("site-theme--{$theme}", false);
        }
    }
}
