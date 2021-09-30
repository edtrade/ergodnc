<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Tag;
class TagControllerTest extends TestCase
{
    use RefreshDatabase;

    
    /**
     * A basic feature test it lists all tags.
     * @test
     * @return void
     */
    public function itListsTags()
    {
        Tag::create(['name' => 'has_ac']);
        Tag::create(['name' => 'has_private_bathroom']);
        Tag::create(['name' => 'has_coffee_machine']);     

        $response = $this->get('/api/tags');

        //dd($response->json('data'));

        $response->assertStatus(200);

        $this->assertNotNull($response->json('data')[0]['id']);
    }
}
