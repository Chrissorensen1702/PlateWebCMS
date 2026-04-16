<?php

namespace Tests\Feature;

use App\Models\CustomerSolution;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CmsDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.laravel_cloud', [
            'token' => null,
            'environment_id' => null,
            'dashboard_url' => null,
            'metrics_period' => '24h',
            'projects' => [
                [
                    'label' => 'Bookingsystem',
                    'token' => null,
                    'environment_id' => null,
                    'dashboard_url' => null,
                    'metrics_period' => '24h',
                ],
                [
                    'label' => 'CMS',
                    'token' => null,
                    'environment_id' => null,
                    'dashboard_url' => null,
                    'metrics_period' => '24h',
                ],
            ],
        ]);
    }

    public function test_guests_are_redirected_to_login_when_accessing_the_cms_dashboard(): void
    {
        $response = $this->get('/cms');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_the_cms_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/cms');

        $response->assertOk();
        $response->assertSee('INTERNT OVERBLIK');
        $response->assertSee('Velkommen tilbage');
        $response->assertSee('HURTIGT OVERBLIK');
        $response->assertSee('Mine sites');
        $response->assertSee('Gaa til site-editor');
    }

    public function test_dashboard_can_show_global_plan_chip_for_single_visible_site(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Plan Tenant',
            'slug' => 'plan-tenant',
            'status' => 'active',
        ]);

        $tenant->users()->attach($user->id, ['role' => 'owner']);

        $plan = Plan::query()->create([
            'name' => 'Template Pro',
            'slug' => 'template-pro',
            'kind' => 'template',
            'headline' => 'Template Pro',
            'summary' => 'En stærk plan.',
            'price_from' => 2999,
            'build_time' => '2 uger',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Site::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'name' => 'Plan Demo',
            'slug' => 'plan-demo',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($user)->get('/cms');

        $response->assertOk();
        $response->assertSee('Abonnement');
        $response->assertSee('Template Pro');
    }

    public function test_dashboard_can_show_fallback_plan_chip_for_customer_without_sites(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $response = $this->actingAs($user)->get('/cms');

        $response->assertOk();
        $response->assertSee('Abonnement');
        $response->assertSee('Ingen plan endnu');
    }

    public function test_dashboard_can_show_saved_solution_when_customer_has_no_sites_yet(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        CustomerSolution::query()->create([
            'user_id' => $user->id,
            'package_key' => 'scale',
            'locations' => 1,
            'staff' => 4,
            'bookings' => 300,
            'sections' => 3,
            'source' => 'pricing_calculator',
        ]);

        $response = $this->actingAs($user)->get('/cms');

        $response->assertOk();
        $response->assertSee('Abonnement');
        $response->assertSee('Studio');
    }

    public function test_dashboard_can_show_plan_count_when_customer_has_multiple_plan_types(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Multi Plan Tenant',
            'slug' => 'multi-plan-tenant',
            'status' => 'active',
        ]);

        $tenant->users()->attach($user->id, ['role' => 'owner']);

        $starterPlan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'kind' => 'template',
            'headline' => 'Starter',
            'summary' => 'Første plan.',
            'price_from' => 699,
            'build_time' => '1 uge',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $scalePlan = Plan::query()->create([
            'name' => 'Scale',
            'slug' => 'scale',
            'kind' => 'template',
            'headline' => 'Scale',
            'summary' => 'Anden plan.',
            'price_from' => 1299,
            'build_time' => '2 uger',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Site::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $starterPlan->id,
            'name' => 'Starter Site',
            'slug' => 'starter-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        Site::query()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => $scalePlan->id,
            'name' => 'Scale Site',
            'slug' => 'scale-site',
            'theme' => 'base',
            'status' => 'ready',
        ]);

        $response = $this->actingAs($user)->get('/cms');

        $response->assertOk();
        $response->assertSee('Abonnement');
        $response->assertSee('2 planer');
    }

    public function test_developers_see_the_dashboard_without_the_client_header(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'name' => 'Chris Sorensen',
            'employment_role' => 'CEO',
        ]);

        $response = $this->actingAs($developer)->get('/cms');

        $response->assertOk();
        $response->assertDontSee('GLOBALT OVERBLIK');
        $response->assertDontSee('Developer dashboard');
        $response->assertSee('Chris Sorensen - CEO');
        $response->assertSee('Bookingsystem');
        $response->assertSee('CMS');
        $response->assertSee('Ikke forbundet');
    }

    public function test_developers_can_see_laravel_cloud_data_when_configured(): void
    {
        Cache::flush();

        config()->set('services.laravel_cloud', [
            'token' => 'cloud-token',
            'environment_id' => 'env-123',
            'dashboard_url' => 'https://cloud.laravel.com/apps/demo/environments/production',
            'metrics_period' => '24h',
            'projects' => [
                [
                    'label' => 'Bookingsystem',
                    'token' => 'cloud-token',
                    'environment_id' => 'env-123',
                    'dashboard_url' => 'https://cloud.laravel.com/apps/demo/environments/production',
                    'metrics_period' => '24h',
                ],
                [
                    'label' => 'CMS',
                    'token' => null,
                    'environment_id' => null,
                    'dashboard_url' => null,
                    'metrics_period' => '24h',
                ],
            ],
        ]);

        Http::fake([
            'https://cloud.laravel.com/api/environments/env-123/deployments*' => Http::response([
                'data' => [
                    [
                        'id' => 'dep-1',
                        'type' => 'deployments',
                        'attributes' => [
                            'status' => 'deployment.succeeded',
                            'branch_name' => 'main',
                            'commit_hash' => 'abc123def456',
                            'started_at' => '2026-04-03T09:30:00Z',
                            'finished_at' => '2026-04-03T09:34:00Z',
                        ],
                    ],
                ],
            ]),
            'https://cloud.laravel.com/api/environments/env-123/metrics*' => Http::response([
                'data' => [
                    'cpu_usage' => ['average' => [17.5]],
                    'memory_usage' => ['average' => [15728640]],
                    'http_response_count' => ['average' => [1000, 240, 0]],
                    'replica_count' => ['average' => [2]],
                ],
                'meta' => [
                    'period' => '24h',
                ],
            ]),
            'https://cloud.laravel.com/api/environments/env-123*' => Http::response([
                'data' => [
                    'id' => 'env-123',
                    'type' => 'environments',
                    'attributes' => [
                        'name' => 'Production',
                        'status' => 'active',
                        'vanity_domain' => 'demo.plateweb.dk',
                    ],
                ],
                'included' => [
                    [
                        'type' => 'applications',
                        'attributes' => [
                            'name' => 'PlateWeb CMS',
                        ],
                    ],
                    [
                        'type' => 'domains',
                        'attributes' => [
                            'domain' => 'demo.plateweb.dk',
                        ],
                    ],
                ],
            ]),
        ]);

        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $response = $this->actingAs($developer)->get('/cms');

        $response->assertOk();
        $response->assertSee('Laravel Cloud');
        $response->assertSee('Bookingsystem');
        $response->assertSee('CMS');
        $response->assertSee('PlateWeb CMS - Production');
        $response->assertSee('Aktivt miljø');
        $response->assertSee('Gennemført');
        $response->assertSee('ABC123D');
        $response->assertSee('18 %');
        $response->assertSee('15,0 MB');
        $response->assertSee('1.240');
    }

    public function test_developers_can_view_the_inquiries_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'kind' => 'template',
            'headline' => 'Starter plan',
            'summary' => 'God til nye websites.',
            'price_from' => 1000,
            'build_time' => '2 uger',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Lead::query()->create([
            'plan_id' => $plan->id,
            'name' => 'Chris Lead',
            'email' => 'lead@example.com',
            'company' => 'PlateWeb',
            'phone' => '12 34 56 78',
            'message' => 'Jeg vil gerne hoere mere.',
            'status' => 'new',
        ]);

        $response = $this->actingAs($developer)->get('/cms/leads');

        $response->assertOk();
        $response->assertSee('Henvendelser');
        $response->assertSee('Chris Lead');
        $response->assertSee('Starter');
    }

    public function test_developers_can_view_the_orders_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $plan = Plan::query()->create([
            'name' => 'Studio',
            'slug' => 'scale',
            'kind' => 'template',
            'headline' => 'Studio plan',
            'summary' => 'Website og booking.',
            'price_from' => 89,
            'build_time' => 'Efter aftale',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $customer = User::factory()->create([
            'role' => 'client',
            'name' => 'Salon Bestilling',
            'email' => 'bestilling@example.com',
        ]);

        CustomerSolution::query()->create([
            'user_id' => $customer->id,
            'plan_id' => $plan->id,
            'package_key' => 'scale',
            'locations' => 1,
            'staff' => 4,
            'bookings' => 300,
            'sections' => 3,
            'source' => 'pricing_calculator',
        ]);

        $response = $this->actingAs($developer)->get('/cms/orders');

        $response->assertOk();
        $response->assertSee('Bestillinger');
        $response->assertSee('Studio');
        $response->assertSee('Salon Bestilling');
    }

    public function test_developers_can_open_the_customers_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $tenant = Tenant::query()->create([
            'name' => 'Salon Maaneskoen',
            'company_email' => 'kontakt@maaneskoen.dk',
            'cvr_number' => '12345678',
            'phone' => '12 34 56 78',
            'slug' => 'salon-maaneskoen',
            'status' => 'active',
        ]);

        $site = Site::query()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Maaneskoen Website',
            'slug' => 'maaneskoen-website',
            'theme' => 'base',
            'status' => 'draft',
            'is_online' => false,
        ]);

        $response = $this->actingAs($developer)->get('/cms/customers');

        $response->assertOk();
        $response->assertSee('Kunder og websites');
        $response->assertSee('Find og rediger for kunde');
        $response->assertSee('Søg kunde');
        $response->assertSee('Søg på navn, CVR eller e-mail');
        $response->assertSee('Salon Maaneskoen');
        $response->assertSee('Maaneskoen Website');
        $response->assertSee(route('cms.sites.show', $site), false);
    }

    public function test_clients_cannot_view_the_customers_page(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $this->actingAs($user)
            ->get('/cms/customers')
            ->assertForbidden();
    }

    public function test_clients_cannot_view_the_leads_page(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $this->actingAs($user)
            ->get('/cms/leads')
            ->assertForbidden();
    }

    public function test_clients_cannot_view_the_orders_page(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $this->actingAs($user)
            ->get('/cms/orders')
            ->assertForbidden();
    }

    public function test_developers_can_open_the_customer_and_site_create_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $response = $this->actingAs($developer)->get('/cms/customer-sites/create');

        $response->assertOk();
        $response->assertSee('Opret kunde og site');
        $response->assertSee('Virksomhed');
        $response->assertSee('Privatperson');
        $response->assertSee('CVR');
        $response->assertSee('Login-e-mail');
    }

    public function test_read_only_developers_cannot_open_the_customer_and_site_create_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_READ_ONLY,
        ]);

        $this->actingAs($developer)
            ->get('/cms/customer-sites/create')
            ->assertForbidden();
    }

    public function test_developers_can_create_a_customer_and_site_from_the_dashboard(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'kind' => 'template',
            'headline' => 'Starter plan',
            'summary' => 'God til nye websites.',
            'price_from' => 1000,
            'build_time' => '2 uger',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($developer)->post('/cms/customer-sites', [
            'customer_type' => 'company',
            'tenant_name' => 'Salon Maaneskoen',
            'company_email' => 'kontakt@maaneskoen.dk',
            'cvr_number' => '12345678',
            'phone' => '12 34 56 78',
            'contact_name' => 'Chris Kunde',
            'contact_email' => 'kunde@maaneskoen.dk',
            'contact_password' => '3rwbnmm7m',
            'site_name' => 'Maaneskoen Website',
            'site_slug' => '',
            'theme' => 'base',
            'plan_id' => $plan->id,
        ]);

        $tenant = Tenant::query()->where('name', 'Salon Maaneskoen')->first();
        $this->assertNotNull($tenant);

        $site = Site::query()->where('name', 'Maaneskoen Website')->first();
        $this->assertNotNull($site);

        $response->assertRedirect("/cms/sites/{$site->id}");

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Salon Maaneskoen',
            'company_email' => 'kontakt@maaneskoen.dk',
            'cvr_number' => '12345678',
            'phone' => '12 34 56 78',
        ]);

        $customer = User::query()->where('email', 'kunde@maaneskoen.dk')->first();
        $this->assertNotNull($customer);

        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $customer->id,
            'role' => 'owner',
        ]);

        $this->assertDatabaseHas('sites', [
            'id' => $site->id,
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'theme' => 'base',
            'status' => 'draft',
            'is_online' => false,
        ]);

        $this->assertDatabaseHas('site_pages', [
            'site_id' => $site->id,
            'name' => 'Forside',
            'slug' => 'home',
            'is_home' => true,
            'is_published' => true,
        ]);

        $this->assertDatabaseHas('site_pages', [
            'site_id' => $site->id,
            'name' => 'Kontakt',
            'slug' => 'kontakt',
            'is_home' => false,
            'is_published' => true,
        ]);

        $this->assertDatabaseHas('site_page_drafts', [
            'site_id' => $site->id,
            'slug' => 'home',
            'is_home' => true,
        ]);
    }

    public function test_developers_can_create_a_private_customer_without_cvr(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
        ]);

        $response = $this->actingAs($developer)->post('/cms/customer-sites', [
            'customer_type' => 'private',
            'tenant_name' => 'Mette Jensen',
            'company_email' => 'mette@example.com',
            'phone' => '12 34 56 78',
            'contact_name' => 'Mette Jensen',
            'contact_email' => 'mette.login@example.com',
            'contact_password' => '3rwbnmm7m',
            'site_name' => 'Mette Website',
            'site_slug' => '',
            'theme' => 'base',
            'plan_id' => '',
        ]);

        $tenant = Tenant::query()->where('name', 'Mette Jensen')->first();
        $this->assertNotNull($tenant);

        $site = Site::query()->where('name', 'Mette Website')->first();
        $this->assertNotNull($site);

        $response->assertRedirect("/cms/sites/{$site->id}");

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Mette Jensen',
            'company_email' => 'mette@example.com',
            'cvr_number' => null,
        ]);
    }

    public function test_non_developers_cannot_create_customer_sites_from_the_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $response = $this->actingAs($user)->post('/cms/customer-sites', [
            'customer_type' => 'company',
            'tenant_name' => 'Blocked Customer',
            'site_name' => 'Blocked Site',
            'theme' => 'base',
        ]);

        $response->assertForbidden();
    }

    public function test_non_developers_cannot_open_the_customer_and_site_create_page(): void
    {
        $user = User::factory()->create([
            'role' => 'client',
        ]);

        $response = $this->actingAs($user)->get('/cms/customer-sites/create');

        $response->assertForbidden();
    }
}
