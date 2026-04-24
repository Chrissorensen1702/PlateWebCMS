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
        $response->assertSee('Vi starter forfra med faste pakker');
        $response->assertSee('Det gamle pakke-admin er sat på pause');
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

    public function test_full_access_developers_cannot_create_plans_while_the_system_is_paused(): void
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

        $response->assertRedirect(route('cms.plans.index'));
        $response->assertSessionHas('status');
        $this->assertDatabaseMissing('plans', [
            'name' => 'Premium template',
        ]);
    }

    public function test_full_access_developers_cannot_update_plans_while_the_system_is_paused(): void
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
        $response->assertSessionHas('status');

        $plan->refresh();

        $this->assertSame('Starter', $plan->name);
        $this->assertSame('starter', $plan->slug);
        $this->assertSame('template', $plan->kind);
        $this->assertSame('Start let', $plan->headline);
        $this->assertSame('Kort summary', $plan->summary);
        $this->assertSame(1999, $plan->price_from);
        $this->assertSame('1 uge', $plan->build_time);
        $this->assertTrue($plan->is_active);
        $this->assertSame(1, $plan->sort_order);
        $this->assertSame([
            'Kontakt',
            'Hero',
        ], $plan->features);
    }
}
