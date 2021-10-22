<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HistoryUser;

class HistoryUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        HistoryUser::factory()->count(30)->create();
    }
}
