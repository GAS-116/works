<?php

namespace Database\Factories;

use App\Models\Calendar;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalendarFactory extends Factory
{
    protected $model = Calendar::class;

    public function definition(): array
    {

        foreach (range(0,rand(1,5)) as $item){
            $participant[] = User::all()->random()->id;
        }

        $live_stream = [
            'stream_key' => $this->faker->word,
            'stream_link' => $this->faker->url,
        ];


        return [
            'title' => $this->faker->sentence(),
            'note' => $this->faker->text(255),
            'start_date' => Carbon::today()->addDays(rand(1, 10)),
            'end_date' => Carbon::today()->addDays(rand(10, 15)),
            'password' => $this->faker->password,
            'user_id' => function () {
                return User::all()->random()->id;
            },
            'participants' => json_encode($participant),
            'is_lobby' => $this->faker->boolean(),
            'live_stream' => json_encode($live_stream)
        ];
    }
}
