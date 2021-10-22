<?php


namespace Database\Factories;

use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class SessionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected  $model = Session::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $date = Carbon::now()->addDays(rand(1, 10))->addMinutes(rand(1, 50));

        return [
            'user_id' => function(){
                return \App\Models\User::all()->random()->id;
            },
            'room_id' => function(){
                return \App\Models\Room::all()->random()->id;
            },
            'active' => $this->faker->boolean,
            'start_time' => $date,
            'finish_time' => $date->addMinutes(rand(1, 50)),
            'time' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
