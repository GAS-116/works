<?php


namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Models\HistoryMeeting;
use App\Models\HistoryUser;
use App\Models\Session;
use App\Models\User;

class TestController extends Controller
{
    public function test ()
    {
        $room_id = "9470faa6-8c25-48d5-a52c-3253a8ed9359";

        /*$room_data = HistoryMeeting::where('room_id', $room_id)
            ->orderBy('start_time', 'DESC')
            ->first();

        $res = $room_data->updateRoomForFinishMeeting($room_data->start_time);*/

//        $sql = "SELECT `user_id`, SUM(`time`) AS user_sum FROM `sessions` WHERE `room_id` = {$room_id} GROUP BY `user_id`";

        $sessions = Session::selectRaw('SUM(time) AS user_time')
            ->addSelect('user_id')
            ->with('user')
            ->where('room_id', $room_id)
            ->groupBy('user_id')
            ->get();

        foreach ($sessions as $item)
        {
            HistoryUser::create([
                'room_id' => $room_id,
                'user_id' => $item->user_id,
                'time' => $item->user_time,
            ]);

            $item->user->update([
                'total_time' => $item->user->total_time + $item->user_time,
            ]);

            $total_time = $item->user->total_time + $item->user_time;
            User::where('id', $item->user_id)->update(['total_time' => $total_time]);
        }
    }
}
