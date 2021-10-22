<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'id' => '00000000-1000-1000-1000-000000000000'
        ]);

        User::factory()->create([
            'id' => '00000000-2000-2000-2000-000000000000'
        ]);

        User::factory()->count(10)->create();
    }
}
