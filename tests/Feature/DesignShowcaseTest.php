<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignShowcaseTest extends TestCase
{
    public function test_design_showcase_lists_themes_and_homepage_previews(): void
    {
        $response = $this->get('/designs');

        $response->assertOk();
        $response->assertSeeText('Se designs');
        $response->assertSeeText('Base');
        $response->assertSeeText('Editorial');
        $response->assertSeeText('Midnight');
        $response->assertSeeText('Se preview');
        $response->assertSeeText('Farveretninger');
        $response->assertSeeText('Vælg det tema, der passer');
        $response->assertSeeText('Tilpas farverne, så de matcher');
        $response->assertSeeText('jeres brand og visuelle identitet.');
        $response->assertSeeText('Få custom design til Studio-pris inkl. 6 måneder gratis.');
        $response->assertSeeText('Læs mere');
    }

    public function test_design_preview_route_can_render_a_selected_theme_homepage(): void
    {
        $response = $this->get('/designs/preview/midnight');

        $response->assertOk();
        $response->assertSee('site-theme--midnight', false);
        $response->assertSeeText('Et moerkt og mere markant udtryk med kontrast og kant.');
        $response->assertSeeText('Signal Lab');
        $response->assertSeeText('Vil du vise mere kant og kontrast pa forsiden?');
    }

    public function test_unknown_design_preview_theme_returns_not_found(): void
    {
        $this->get('/designs/preview/findes-ikke')->assertNotFound();
    }
}
