<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomePageLoadsTest extends TestCase
{
    /** @test */
    public function home_page_returns_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}

