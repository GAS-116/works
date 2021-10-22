<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoomSeeder::class,
            CalendarSeeder::class,
            SessionSeeder::class,
            HistoryMeetingSeeder::class,
            HistoryUserSeeder::class,
        ]);
    }
}
