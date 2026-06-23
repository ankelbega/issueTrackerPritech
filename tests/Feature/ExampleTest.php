<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL is a deliberate redirect to the projects index, the app's
     * main landing page, so it should never return a bare 200.
     */
    public function test_the_application_redirects_to_projects(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/projects');
    }
}
