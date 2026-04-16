<?php

namespace Tests\Feature\Auth;

use App\Models\CustomerSolution;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_customers_with_a_saved_solution_are_redirected_to_it_after_login(): void
    {
        $user = User::factory()->create();
        $plan = Plan::query()->create([
            'name' => 'Studio',
            'slug' => 'studio',
            'kind' => 'template',
            'headline' => 'Studio',
            'summary' => 'Website og booking',
            'price_from' => 89,
            'build_time' => 'Efter aftale',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        CustomerSolution::query()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'package_key' => 'scale',
            'locations' => 1,
            'staff' => 4,
            'bookings' => 300,
            'sections' => 3,
            'source' => 'pricing_calculator',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('customer.solution.show', absolute: false));
        $this->assertDatabaseHas('tenants', [
            'name' => $user->name,
            'company_email' => $user->email,
        ]);
        $this->assertDatabaseHas('sites', [
            'plan_id' => $plan->id,
            'name' => $user->name,
            'theme' => 'base',
        ]);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
