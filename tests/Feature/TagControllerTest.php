<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagControllerTest extends TestCase
{
    /**
     * A basic feature test it lists all tags.
     * @test
     * @return void
     */
    public function itListsTags()
    {
        $response = $this->get('/api/tags');

        //dd($response->json('data'));

        $response->assertStatus(200);

        $this->assertNotNull($response->json('data')[0]['id']);
    }
}
