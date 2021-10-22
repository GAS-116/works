<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HistoryMeeting;

class HistoryMeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HistoryMeeting::factory()->count(30)->create();
    }
}
