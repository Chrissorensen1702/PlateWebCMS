<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadCaptureTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_page_leads_can_be_captured(): void
    {
        $plan = Plan::query()->create([
            'name' => 'Template Start',
            'slug' => 'template-start',
            'kind' => 'template',
            'headline' => 'Kickstart',
            'summary' => 'Kickstart package',
            'price_from' => 7900,
            'build_time' => '1 uge',
        ]);

        $plan->syncFeatures(['CMS']);

        $response = $this->post('/kontakt', [
            'name' => 'Chris Sorensen',
            'email' => 'chris@example.com',
            'company' => 'North Studio',
            'phone' => '12345678',
            'plan_id' => $plan->id,
            'message' => 'Jeg vil gerne have et tilbud paa en templateside.',
        ]);

        $response->assertRedirect('/kontakt#kontakt-form');
        $this->assertDatabaseHas('leads', [
            'email' => 'chris@example.com',
            'plan_id' => $plan->id,
        ]);
    }
}
