<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Tag::create(['name' => 'has_ac']);
        Tag::create(['name' => 'has_private_bathroom']);
        Tag::create(['name' => 'has_coffee_machine']);          
    }
}
