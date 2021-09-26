<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Office;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itReturnsAListOfOffices()
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');

        //$response->assertStatus(200)->dump();
        //$this->assertCount(3,$response->json('data'));

        $response->assertOk();
        $response->assertJsonCount(3,'data');
        $this->assertNotNull($response->json('data')[0]['id']);
    }
}
