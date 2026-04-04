<?php

namespace Tests\Feature;

use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_home_page_renders(): void
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

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Saelg templates og custom builds fra samme platform.');
    }

    public function test_public_marketing_pages_can_be_rendered(): void
    {
        $plans = [
            [
                'name' => 'Template Start',
                'slug' => 'template-start',
                'kind' => 'template',
                'headline' => 'Kickstart',
                'summary' => 'Kickstart package',
                'price_from' => 7900,
                'build_time' => '1 uge',
                'is_active' => true,
                'sort_order' => 1,
                'features' => ['CMS'],
            ],
            [
                'name' => 'Custom Build',
                'slug' => 'custom-build',
                'kind' => 'custom',
                'headline' => 'Specialbygget',
                'summary' => 'Specialbygget site',
                'price_from' => null,
                'build_time' => 'Efter scope',
                'is_active' => true,
                'sort_order' => 2,
                'features' => ['Custom'],
            ],
        ];

        foreach ($plans as $planData) {
            $features = $planData['features'];
            unset($planData['features']);

            $plan = Plan::query()->create($planData);
            $plan->syncFeatures($features);
        }

        $this->get('/templates')->assertOk()->assertSee('Template-pakker der er hurtige at saelge og levere.');
        $this->get('/custom-build')->assertOk()->assertSee('Custom builds med et skraeddersyet udtryk og samme CMS-kerne.');
        $this->get('/kunde-cms')->assertOk()->assertSee('Kundelogin og CMS som en stabil del af leverancen.');
        $this->get('/kontakt')->assertOk()->assertSee('Lad os finde den rigtige pakke til projektet.');
    }
}
