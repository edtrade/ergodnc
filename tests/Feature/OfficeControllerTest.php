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
use Illuminate\Support\Facades\Notification;
use App\Notifications\OfficePendingApprovalNotification;

class OfficeControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;
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
     public function itShowAnOffice()
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

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itCreatesAnOffice()
     {
        $admin = User::factory()->create([
            'email'=>'admin@admin.com'
        ]);
                
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $tag2 = Tag::factory()->create();

        Notification::fake();

        $this->actingAs($user);

        $response = $this->postJson('/api/offices',[
            'title'=> $title = $this->faker->sentence,
            'description'=>$this->faker->paragraph,
            'lat'=>$this->faker->latitude,
            'lng'=>$this->faker->longitude,
            'address_line1'=>$this->faker->address,
            'price_per_day'=>$this->faker->numberBetween(1_000, 8_000),
            'tags'=>[$tag->id, $tag2->id]
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title',$title);
            //->assertJsonCount(2,'data.tags');
        $this->assertDatabaseHas('offices',[
            'title'=>$title
        ]);    

        Notification::assertSentTo([$admin], OfficePendingApprovalNotification::class);
     }             

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itDoesntAllowCreatingAnOfficeIfScopeNotProvided()
     {
        $user = User::factory()->create();

        $token = $user->createToken('test',[]);

        $response = $this->postJson('/api/offices',
            [
                'title'=> $title = $this->faker->sentence,
                'description'=>$this->faker->paragraph,
                'lat'=>$this->faker->latitude,
                'lng'=>$this->faker->longitude,
                'address_line1'=>$this->faker->address,
                'price_per_day'=>$this->faker->numberBetween(1_000, 8_000)
            ],
            [
                'Authorization' => 'Bearer '.$token->plainTextToken
            ]
        );

        $response->assertStatus(403);   
     }  
    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itUpdatesAnOffice()
     {
        $user = User::factory()->create();

        $tags = Tag::factory(3)->create();

        $anotherTag = Tag::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office->tags()->attach($tags);

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id,[
            'title'=> $title = $this->faker->sentence,
            'description'=>$this->faker->paragraph,
            'tags'=>[$tags[0]->id, $anotherTag->id]
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title',$title)
            ->assertJsonPath('data.tags.0.id',$tags[0]->id)
            ->assertJsonPath('data.tags.1.id',$anotherTag->id);
        $this->assertDatabaseHas('offices',[
            'title'=>$title
        ]);    
     }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itUpdatesFeaturedImageOfAnOffice()
     {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path'=>'sum_image.jpg'
        ]);

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id,[
            'title'=> $title = $this->faker->sentence,
            'description'=>$this->faker->paragraph,
            'featured_image_id'=>$image->id
        ]);

        //
        $response->assertOk()
            ->assertJsonPath('data.title',$title)
            ->assertJsonPath('data.featured_image_id',$image->id);
        //    
        $this->assertDatabaseHas('offices',[
            'title'=>$title
        ]);    
     }  
    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itDoesntUpdatesFeaturedImageOfAnotherOffice()
     {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office2 = Office::factory()->for($user)->create();

        $image = $office2->images()->create([
            'path'=>'sum_image.jpg'
        ]);


        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id,[
            'featured_image_id'=>$image->id
        ]);

        //
        $response->assertStatus(422);    
     }       

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itDosentUpdatesAnOfficeThatDoesntBelongToUser()
     {
        $user = User::factory()->create();

        $anotherUser = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($anotherUser);

        $response = $this->putJson('/api/offices/'.$office->id,[
            'title'=> $title = $this->faker->sentence,
            'description'=>$this->faker->paragraph
        ]);

        $response->assertStatus(403);   
     }   

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itMarksOfficeAsPendingWhenDirty()
     {
        $admin = User::factory()->create([
            'is_admin'=>true
        ]);

        Notification::fake();

        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->putJson('/api/offices/'.$office->id,[
            'lat'=>$this->faker->latitude
        ]);

        $response->assertOK();
        $response->assertJsonPath('data.approval_status',Office::APPROVAL_PENDING);   

        Notification::assertSentTo([$admin], OfficePendingApprovalNotification::class);
     }  

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itCanDeleteOffices()
     {

        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $this->actingAs($user);

        $response = $this->delete('/api/offices/'.$office->id);

        $response->assertOK();

        $this->assertSoftDeleted($office);
     }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */  
     public function itCannotDeleteOfficesThatHasReservations()
     {

        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $reservations = Reservation::factory(3)->for($office)->create();

        $this->actingAs($user);

        $response = $this->deleteJson('/api/offices/'.$office->id);

        $response->assertUnprocessable();

        $this->assertDatabaseHas($office,[
            'id'=>$office->id,
            'deleted_at'=>null
        ]);
     } 

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
     public function itListsOfficesIncludesHiddenAndUnapprovedForCurrentUser()
     {
        $user = User::factory()->create();

        Office::factory(3)->for($user)->create();

        Office::factory()->hidden()->for($user)->create();

        Office::factory()->pending()->for($user)->create();

        $this->actingAs($user);

        $response = $this->get('/api/offices?user_id='.$user->id);

        $response->assertOk();

        $response->assertJsonCount(5,'data');
     }                 
}
