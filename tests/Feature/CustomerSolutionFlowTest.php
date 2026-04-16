<?php

namespace Tests\Feature;

use App\Models\CustomerSolution;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerSolutionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_capture_a_solution_register_and_view_it_later(): void
    {
        $plans = $this->createSalesPlans();

        $response = $this->post(route('customer.solution.capture'), [
            'package_key' => 'scale',
            'locations' => 2,
            'staff' => 8,
            'bookings' => 1200,
            'sections' => 4,
        ]);

        $response->assertRedirect(route('register', absolute: false));

        $this->get(route('register'))
            ->assertOk()
            ->assertSeeText('Din gemte løsning')
            ->assertSeeText('Studio');

        $registerResponse = $this->post(route('register'), [
            'name' => 'Salon Test',
            'email' => 'salon@example.com',
            'phone' => '+45 12 34 56 78',
            'cvr_number' => '12345678',
            'registration_note' => 'Ring gerne med en kort gennemgang.',
            'wants_callback' => '1',
            'accept_terms' => '1',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $registerResponse->assertRedirect(route('customer.solution.show', absolute: false));

        $this->assertDatabaseHas('customer_solutions', [
            'user_id' => auth()->id(),
            'plan_id' => $plans['scale']->id,
            'package_key' => 'scale',
            'locations' => 2,
            'staff' => 8,
            'bookings' => 1200,
            'sections' => 4,
            'source' => 'pricing_calculator',
        ]);

        $this->assertDatabaseHas('tenants', [
            'name' => 'Salon Test',
            'company_email' => 'salon@example.com',
            'status' => 'active',
        ]);

        $tenant = Tenant::query()->firstWhere('company_email', 'salon@example.com');

        $this->assertNotNull($tenant);
        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'role' => 'owner',
        ]);
        $this->assertDatabaseHas('sites', [
            'tenant_id' => $tenant->id,
            'plan_id' => $plans['scale']->id,
            'name' => 'Salon Test',
            'theme' => 'base',
        ]);

        $this->get(route('customer.solution.show'))
            ->assertOk()
            ->assertSeeText('Studio')
            ->assertSeeText('Juster beregneren')
            ->assertSeeText('Bookinger/år');
    }

    public function test_authenticated_customer_can_save_a_solution_directly_to_their_account(): void
    {
        $plans = $this->createSalesPlans();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('customer.solution.capture'), [
            'package_key' => 'launch',
            'locations' => 1,
            'staff' => 4,
            'bookings' => 300,
            'sections' => 5,
            'traffic_tier' => 'high',
            'lead_module' => 1,
            'seo_copy' => 0,
        ]);

        $response->assertRedirect(route('customer.solution.show', absolute: false));

        $this->assertDatabaseHas('customer_solutions', [
            'user_id' => $user->id,
            'plan_id' => $plans['launch']->id,
            'package_key' => 'launch',
            'locations' => 1,
            'staff' => 4,
            'bookings' => 300,
            'sections' => 5,
            'source' => 'pricing_calculator',
        ]);

        $solution = CustomerSolution::query()->firstWhere('user_id', $user->id);

        $this->assertNotNull($solution);
        $this->assertSame([
            'traffic_tier' => 'high',
            'lead_module' => true,
            'seo_copy' => false,
            'billing_cycle' => 'monthly',
        ], $solution->package_options);

        $this->assertDatabaseHas('tenants', [
            'name' => $user->name,
            'company_email' => $user->email,
            'status' => 'active',
        ]);

        $tenant = Tenant::query()->firstWhere('company_email', $user->email);

        $this->assertNotNull($tenant);
        $this->assertDatabaseHas('tenant_user', [
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
        $this->assertDatabaseHas('sites', [
            'tenant_id' => $tenant->id,
            'plan_id' => $plans['launch']->id,
            'name' => $user->name,
            'theme' => 'base',
        ]);

        $this->get(route('customer.solution.show'))
            ->assertOk()
            ->assertSeeText('Sider')
            ->assertSeeText('Forventet trafik')
            ->assertSeeText('Nyhedsbrev / leadmodul');
    }

    public function test_booking_only_solution_does_not_auto_create_a_website(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('customer.solution.capture'), [
            'package_key' => 'platebook',
            'locations' => 1,
            'staff' => 4,
            'bookings' => 300,
            'sections' => 3,
        ]);

        $response->assertRedirect(route('customer.solution.show', absolute: false));

        $this->assertDatabaseHas('customer_solutions', [
            'user_id' => $user->id,
            'package_key' => 'platebook',
        ]);

        $this->assertDatabaseCount('tenants', 0);
        $this->assertDatabaseCount('sites', 0);
    }

    /**
     * @return array<string, Plan>
     */
    private function createSalesPlans(): array
    {
        $plans = [
            'launch' => [
                'name' => 'Atelier',
                'slug' => 'atelier',
                'kind' => 'template',
                'headline' => 'Hurtig start',
                'summary' => 'Standard hjemmeside',
                'price_from' => 69,
                'build_time' => '1-2 uger',
                'is_active' => true,
                'sort_order' => 1,
            ],
            'scale' => [
                'name' => 'Studio',
                'slug' => 'scale',
                'kind' => 'template',
                'headline' => 'Mere branding og booking',
                'summary' => 'Website og booking',
                'price_from' => 89,
                'build_time' => 'Efter aftale',
                'is_active' => true,
                'sort_order' => 2,
            ],
            'signature' => [
                'name' => 'Signature',
                'slug' => 'custom',
                'kind' => 'custom',
                'headline' => 'Specialbygget',
                'summary' => 'Efter tilbud',
                'price_from' => 5000,
                'build_time' => 'Efter scope',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        return collect($plans)
            ->map(fn (array $attributes): Plan => Plan::query()->create($attributes))
            ->all();
    }
}
