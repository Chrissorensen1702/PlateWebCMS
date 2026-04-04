<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CmsPlanManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_access_developers_can_view_the_plan_management_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_FULL,
        ]);

        $response = $this->actingAs($developer)->get(route('cms.plans.index'));

        $response->assertOk();
        $response->assertSee('Pakker i systemet');
        $response->assertSee('Opret en ny pakke');
    }

    public function test_customer_managers_cannot_view_the_plan_management_page(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_CUSTOMER_MANAGER,
        ]);

        $this->actingAs($developer)
            ->get(route('cms.plans.index'))
            ->assertForbidden();
    }

    public function test_full_access_developers_can_create_plans(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_FULL,
        ]);

        $response = $this->actingAs($developer)->post(route('cms.plans.store'), [
            'name' => 'Premium template',
            'slug' => '',
            'kind' => 'template',
            'headline' => 'Den skarpeste template-pakke.',
            'summary' => 'En gennemarbejdet pakke til kunder der vil hurtigt i luften.',
            'price_from' => 4999,
            'build_time' => '2 uger',
            'sort_order' => 3,
            'is_active' => '1',
            'features' => "Forside\nKontakt\nBookingflow",
        ]);

        $plan = Plan::query()->where('name', 'Premium template')->first();

        $response->assertRedirect(route('cms.plans.index'));
        $this->assertNotNull($plan);

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'slug' => 'premium-template',
            'kind' => 'template',
            'price_from' => 4999,
            'build_time' => '2 uger',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $this->assertSame([
            'Forside',
            'Kontakt',
            'Bookingflow',
        ], $plan->fresh()->features);
    }

    public function test_full_access_developers_can_update_plans(): void
    {
        $developer = User::factory()->create([
            'role' => 'developer',
            'developer_access' => User::DEVELOPER_ACCESS_FULL,
        ]);

        $plan = Plan::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'kind' => 'template',
            'headline' => 'Start let',
            'summary' => 'Kort summary',
            'price_from' => 1999,
            'build_time' => '1 uge',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $plan->syncFeatures(['Kontakt', 'Hero']);

        $response = $this->actingAs($developer)->patch(route('cms.plans.update', $plan), [
            'name' => 'Starter Plus',
            'slug' => 'starter-plus',
            'kind' => 'custom',
            'headline' => 'Mere fleksibel pakke',
            'summary' => 'Opdateret beskrivelse',
            'price_from' => 2999,
            'build_time' => '3 uger',
            'sort_order' => 5,
            'is_active' => '0',
            'features' => "Custom design\nUdvidet indhold",
        ]);

        $response->assertRedirect(route('cms.plans.index'));

        $this->assertDatabaseHas('plans', [
            'id' => $plan->id,
            'name' => 'Starter Plus',
            'slug' => 'starter-plus',
            'kind' => 'custom',
            'headline' => 'Mere fleksibel pakke',
            'summary' => 'Opdateret beskrivelse',
            'price_from' => 2999,
            'build_time' => '3 uger',
            'is_active' => false,
            'sort_order' => 5,
        ]);

        $this->assertSame([
            'Custom design',
            'Udvidet indhold',
        ], $plan->fresh()->features);
    }
}
