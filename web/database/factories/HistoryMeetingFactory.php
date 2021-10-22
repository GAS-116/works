<?php

namespace Database\Factories;

use App\Models\HistoryMeeting;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryMeetingFactory extends Factory
{
    protected $model = HistoryMeeting::class;

    public function definition(): array
    {
        $date = Carbon::now()->addDays(rand(1, 10))->addMinutes(rand(1, 50));

        return [
            'room_id' => function () {
                return Room::all()->random()->id;
            },
            'start_time' => $date,
            'finish_time' => $date->addMinutes(rand(1, 50)),
            'total_time' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
