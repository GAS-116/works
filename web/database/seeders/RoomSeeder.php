<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();

        for($a = 0; $a <= 5; $a++){
            $room = Room::factory()->create();
            $room->roomUsers()->attach($users->random());
        }
    }
}
