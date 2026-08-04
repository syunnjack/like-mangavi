<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_google_tags_are_rendered_when_configured(): void
    {
        config([
            'services.ga4.id' => 'G-TEST123',
            'services.ga4.site_verification' => 'verification-token',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('gtag/js?id=G-TEST123', false);
        $response->assertSee("gtag('config', 'G-TEST123')", false);
        $response->assertSee(
            '<meta name="google-site-verification" content="verification-token">',
            false
        );
    }

    public function test_google_tags_are_omitted_when_not_configured(): void
    {
        config([
            'services.ga4.id' => null,
            'services.ga4.site_verification' => null,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('googletagmanager.com', false);
        $response->assertDontSee('google-site-verification', false);
    }
}
