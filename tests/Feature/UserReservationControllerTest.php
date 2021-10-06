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
    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itListReservationsByDateRange()
    {
        $user = User::factory()->create();

        $fromDate = '2021-03-03';
        $toDate ='2021-04-04';

        Reservation::factory()->for($user)->create([
            'start_date'=>'2021-03-01',
            'end_date'=>'2021-03-15'
        ]);

        Reservation::factory()->for($user)->create([
            'start_date'=>'2021-03-25',
            'end_date'=>'2021-04-15'
        ]);

        Reservation::factory()->for($user)->create([
            'start_date'=>'2021-03-25',
            'end_date'=>'2021-04-01'
        ]);        

        //outside range
        Reservation::factory()->for($user)->create([
            'start_date'=>'2021-02-01',
            'end_date'=>'2021-02-15'
        ]);                

        $this->actingAs($user);

        $response = $this->get("/api/reservations?from_date={$fromDate}&to_date={$toDate}");

        $response->assertStatus(200);

        $response->assertJsonCount(3,'data')
            ->assertJsonStructure(['data','meta','links'])
            ->assertJsonStructure(['data'=>['*'=>['id','office']]]);
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itFiltersResultsByStatus()
    {
        $user = User::factory()->create();


        $activeReservation = Reservation::factory()->for($user)->create([
            'status'=>Reservation::STATUS_ACTIVE
        ]);

        Reservation::factory()->for($user)->create([
            'status'=>Reservation::STATUS_CANCELLED
        ]);

        Reservation::factory()->for($user)->create([
            'status'=>Reservation::STATUS_CANCELLED
        ]);        

        //outside range
        Reservation::factory()->for($user)->create([
            'status'=>Reservation::STATUS_CANCELLED
        ]);  
        $this->actingAs($user);

        $status = Reservation::STATUS_ACTIVE;

        $response = $this->get("/api/reservations?status={$status}");

        $response->assertStatus(200)
            ->assertJsonCount(1,'data')
            ->assertJsonPath('data.0.id',$activeReservation->id);   
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itFiltersResultsByOffice()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $reservations = Reservation::factory(2)->for($office)->for($user)->create();  

        $others = Reservation::factory(3)->for($user)->create();

        $this->actingAs($user);

        $response = $this->get("/api/reservations?office_id={$office->id}");    

        $response->assertStatus(200)
            ->assertJsonCount(2,'data')
            ->assertJsonPath('data.0.office.id',$office->id);                  
    }    

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itMakesReservations()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create([
            'price_per_day'=>1000,
            'monthly_discount'=> 10
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations',[
            'office_id'=>$office->id,
            'start_date'=>now()->addDays(2),
            'end_date'=>now()->addDays(41)
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.price',36000)
            ->assertJsonPath('data.status',Reservation::STATUS_ACTIVE)
            ->assertJsonPath('data.user_id',$user->id)
            ->assertJsonPath('data.office_id',$office->id);


    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itCannotMakeReservationOnNonExistantOffice()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations',[
            'office_id'=>1000001,
            'start_date'=>now()->addDays(2),
            'end_date'=>now()->addDays(41)
        ]);      

        $response->assertStatus(422);
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itCannotMakeReservationOnOfficeThatBelongsToHimself()
    {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations',[
            'office_id'=>$office->id,
            'start_date'=>now()->addDays(2),
            'end_date'=>now()->addDays(41)
        ]);  

        $response->assertStatus(422)
            ->assertJsonValidationErrors(["office_id" => "Cannot make reservation on your own office"]);
    }    

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itCannotMakeReservationsForLessThan2Days()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations',[
            'office_id'=>$office->id,
            'start_date'=>now()->addDays(2),
            'end_date'=>now()->addDays(2)
        ]);  

        $response->assertStatus(422);        
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itCanMakeReservationsForMoreThan2Days()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        $this->actingAs($user);

        $response = $this->postJson('/api/reservations',[
            'office_id'=>$office->id,
            'start_date'=>now()->addDays(2),
            'end_date'=>now()->addDays(3)
        ]);  

        $response->assertCreated();        
    }  

    /**
     * A basic feature test example.
     * @test
     * @return void
     */    
    public function itCannotMakeAReservationOnAlreadyReservedOffice()
    {
        $user = User::factory()->create();

        $office = Office::factory()->create();

        Reservation::factory()->for($office)->create([
            'start_date'=>'2021-03-01',
            'end_date'=>'2021-03-15'
        ]);
        
        $this->actingAs($user);

        $response = $this->postJson('/api/reservations',[
            'office_id'=>$office->id,
            'start_date'=>'2021-03-02',
            'end_date'=>'2021-03-07'
        ]);   

        $response->assertStatus(422);   

    }
}
