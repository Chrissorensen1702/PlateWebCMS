<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Sales\CustomerSolutionController;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_redirects_to_pricing_guide_without_a_pending_solution(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('templates').'#pricing-guide');
    }

    public function test_users_can_register_after_creating_a_pending_solution(): void
    {
        $plan = $this->createLaunchPlan();

        $response = $this
            ->withSession([
                CustomerSolutionController::SESSION_KEY => $this->pendingSelection($plan),
            ])
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+45 12 34 56 78',
                'cvr_number' => '12345678',
                'registration_note' => 'Vi vil gerne have en gennemgang af løsningen.',
                'wants_callback' => '1',
                'accept_terms' => '1',
                'password' => 'password',
            ]);

        $this->assertAuthenticated();
        $user = User::query()->firstWhere('email', 'test@example.com');
        $this->assertNotNull($user);
        $this->assertSame('+45 12 34 56 78', $user->phone);
        $this->assertSame('12345678', $user->cvr_number);
        $this->assertSame('Vi vil gerne have en gennemgang af løsningen.', $user->registration_note);
        $this->assertTrue($user->wants_callback);
        $this->assertNotNull($user->accepted_terms_at);
        $this->assertDatabaseHas('customer_solutions', [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'package_key' => 'launch',
        ]);
        $response->assertRedirect(route('customer.solution.show', absolute: false));
    }

    public function test_registration_redirects_to_pricing_guide_when_posted_without_a_pending_solution(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+45 12 34 56 78',
            'accept_terms' => '1',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('templates').'#pricing-guide');
        $this->assertDatabaseMissing('users', [
            'email' => 'test@example.com',
        ]);
        $this->assertGuest();
    }

    public function test_registration_requires_acceptance_of_terms(): void
    {
        $plan = $this->createLaunchPlan();

        $response = $this
            ->withSession([
                CustomerSolutionController::SESSION_KEY => $this->pendingSelection($plan),
            ])
            ->from('/register')
            ->post('/register', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '+45 12 34 56 78',
                'password' => 'password',
            ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('accept_terms');
        $this->assertGuest();
    }

    private function createLaunchPlan(): Plan
    {
        return Plan::query()->create([
            'name' => 'Atelier',
            'slug' => 'atelier',
            'kind' => 'template',
            'headline' => 'Hurtig start',
            'summary' => 'Standard hjemmeside',
            'price_from' => 69,
            'build_time' => '1-2 uger',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function pendingSelection(Plan $plan): array
    {
        return [
            'package_key' => 'launch',
            'plan_id' => $plan->id,
            'locations' => 1,
            'staff' => 3,
            'bookings' => 300,
            'sections' => 2,
            'traffic_tier' => 'medium',
            'lead_module' => false,
            'seo_copy' => false,
            'billing_cycle' => 'monthly',
            'package_options' => [
                'traffic_tier' => 'medium',
                'lead_module' => false,
                'seo_copy' => false,
                'billing_cycle' => 'monthly',
            ],
        ];
    }
}
