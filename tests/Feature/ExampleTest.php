<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_to_the_treasurer_workspace(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/treasurer/dashboard');
    }
}
