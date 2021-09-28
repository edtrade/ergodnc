<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Office;
use App\Models\User;
use App\Models\Reservation;
use App\Models\Tag;
use App\Models\Image;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itReturnsAPaginatedListOfOffices()
    {
        Office::factory(3)->create();

        $response = $this->get('/api/offices');

        //$response->assertStatus(200)->dump();
        //$this->assertCount(3,$response->json('data'));

        $response->assertOk();
        $response->assertJsonCount(3,'data');
        $this->assertNotNull($response->json('meta'));
        $this->assertNotNull($response->json('data')[0]['id']);
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itOnlyListOfficesThatAreNotHiddenAndApproved()
    {
        //create 2 not hidden and approved only
        Office::factory()->create([
            'hidden'=>true
        ]);
        Office::factory()->create([
            'approval_status'=>Office::APPROVAL_PENDING,
        ]);  
        Office::factory()->create([
            'approval_status'=>Office::APPROVAL_APPROVED,
        ]);  
        Office::factory()->create([
            'hidden'=>false
        ]);        

        $response = $this->get('/api/offices');

        $response->assertOk();
        $response->assertJsonCount(2,'data');

    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */    
    public function itFiltersByUserId()
    {
        Office::factory(3)->create();
        
        $host = User::factory()->create();

        $office = Office::factory()->for($host)->create();

        $response = $this->get('/api/offices?user_id='.$host->id);  

        //dd($response->json('data'));

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id,$response->json('data')[0]['id']);             
    }
    /**
     * A basic feature test example.
     * @test
     * @return void
     */    
    public function itFiltersByVisitorId()
    {
        Office::factory(3)->create();
        
        $user = User::factory()->create();

        $office = Office::factory()->create();

        Reservation::factory()->for(Office::factory())->create();

        Reservation::factory()->for($office)->for($user)->create();

        $response = $this->get('/api/offices?visitor_id='.$user->id);  

        //dd($response->json('data'));

        $response->assertOk();
        $response->assertJsonCount(1,'data');
        $this->assertEquals($office->id,$response->json('data')[0]['id']);             
    }   

    /**
     * A basic feature test example.
     * @test
     * @return void
     */    
     public function itIncludesImagesTagsAndUser()
     {
         $user = User::factory()->create();

         $office = Office::factory()->for($user)->create();

         $tag = Tag::factory()->create();

         $office->tags()->attach($tag);

         $office->images()->create([
            'path'=>'image.jpg'
         ]);

         $response = $this->get('/api/offices');

         $response->assertOk();

         $this->assertIsArray($response->json('data')[0]['tags']);
         $this->assertIsArray($response->json('data')[0]['images']);
         $this->assertEquals($user->id, $response->json('data')[0]['user']['id']);
     } 
    /**
     * A basic feature test example.
     * @test
     * @return void
     */   
     public function itReturnsTheNumberOfReservation()
     {
        $office = Office::factory()->create();
       
        Reservation::factory()->for($office)->create([
            'status'=>Reservation::STATUS_ACTIVE
        ]);

        Reservation::factory()->for($office)->create([
            'status'=>Reservation::STATUS_CANCELLED
        ]);    
 
        $response = $this->get('/api/offices');

        $response->assertOk();
        $this->assertEquals(1, $response->json('data')[0]['reservations_count']);
     }     

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itOrdersByDistanceWhenCoordinatesAreProvided()
     {
        $lat = 38.7206613;
        $lng = -9.1604478;

        $office2 = Office::factory()->create([
            'lat' => 39.7405172,
            'lng' => -8.7703753,
            'title' => 'Leiria'
        ]); 

        $office = Office::factory()->create([
            'lat' => 39.0775388,
            'lng' => -9.2812663,
            'title' => 'Torres Vedras'
        ]);

       

        $response = $this->get('/api/offices?lat='.$lat.'&lng='.$lng);

        $response->assertOk();
        $this->assertEquals($office->title,$response->json('data')[0]['title']);

        $response = $this->get('/api/offices');
        $response->assertOk();
        $this->assertEquals($office2->title,$response->json('data')[0]['title']);
     }
    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itShowAOffice()
     {
        $office = Office::factory()->create();

         $tag = Tag::factory()->create();

         $office->tags()->attach($tag);

         $office->images()->create([
            'path'=>'image.jpg'
         ]);

        Reservation::factory()->for($office)->create([
            'status'=>Reservation::STATUS_ACTIVE
        ]);

        Reservation::factory()->for($office)->create([
            'status'=>Reservation::STATUS_CANCELLED
        ]);             

        $response = $this->get('/api/offices/'.$office->id);
        $response->assertOk();
        $this->assertEquals(1, $response->json('data')['reservations_count']);
         $this->assertIsArray($response->json('data')['tags']);
         $this->assertIsArray($response->json('data')['images']);        

     }                  
}
