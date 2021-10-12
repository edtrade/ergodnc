<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class OfficeImageControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itUploadsAndStoresAnImageForAnOffice()
    {
        Storage::fake();

        $office = Office::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post("/api/offices/{$office->id}/images",[
            'image'=> UploadedFile::fake()->image('fake.jpg')
        ]);

        $response->assertCreated();

        //dd($response->json('data'));
        Storage::assertExists($response->json('data.path'));
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itDeletesAnImage()
    {
        Storage::fake();

        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();


        UploadedFile::fake()->image('fake.jpg');

        $image = $office->images()->create([
            'path'=>'fake.jpg'
        ]);

        $image2 = $office->images()->create([
            'path'=>'sum_image.jpg'
        ]);


        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");

        $response->assertOk();

        $this->assertModelMissing($image);

        Storage::assertMissing('fake.jpg');
    }  
      

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itCannotDeletesAnOfficeOnlyImage()
    {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $image = $office->images()->create([
            'path'=>'sum_image.jpg'
        ]);


        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");

        $response->assertStatus(422);

        $response->assertJsonValidationErrors(['image'=>'Cannot delete the only image']);
    }

    /**
     * A basic feature test example.
     * @test
     * @return void
     */
    public function itCannotDeletesAnotherOfficeImage()
    {
        $user = User::factory()->create();

        $office = Office::factory()->for($user)->create();

        $office2 = Office::factory()->for($user)->create();

        $image = $office2->images()->create([
            'path'=>'sum_image.jpg'
        ]);


        $this->actingAs($user);

        $response = $this->deleteJson("/api/offices/{$office->id}/images/{$image->id}");

        $response->assertStatus(404);
    }    

}
