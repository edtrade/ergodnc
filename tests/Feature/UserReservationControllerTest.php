<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Reservation;
use App\Models\Office;
use App\Models\User;

class UserReservationControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itListReservationsThatBelongToTheUser()
    {
        $user = User::factory()->create();

        $reservations = Reservation::factory(2)->for($user)->create();

        $others = Reservation::factory(3)->create();

        $this->actingAs($user);

        $response = $this->get('/api/reservations');

        $response->assertStatus(200);

        $response->assertJsonCount(2,'data')
            ->assertJsonStructure(['data','meta','links'])
            ->assertJsonStructure(['data'=>['*'=>['id','office']]]);
    }
}
