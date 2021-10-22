<?php


namespace Database\Factories;

use App\Models\HistoryUser;
use App\Models\User;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoryUserFactory extends Factory
{
    protected $model = HistoryUser::class;

    public function definition(): array
    {
        $date = Carbon::now()->addDays(rand(1, 10))->addMinutes(rand(1, 50));

        return [
            'room_id' => function () {
                return Room::all()->random()->id;
            },
            'user_id' => function () {
                return User::all()->random()->id;
            },
            'time' => $this->faker->numberBetween(100, 10000),
        ];
    }
}
