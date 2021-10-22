<?php

namespace App\Services;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DataService
{
    public static function getLinkByUser($data)
    {
        $room_slug = $data->slug;
        $user_slug = $data->roomUsers[0]->slug;

        return $user_slug . '-' . $room_slug;
    }

    public static function convertLinkToArray($link_data)
    {
        $link_array = explode('-', $link_data);

        return [
            'user_slug' => $link_array[0] . '-' . $link_array[1],
            'room_slug' => $link_array[2] . '-' . $link_array[3],
        ];
    }

    public static function getTimeDifference ($time_start, $time_end)
    {
        $time_data = Carbon::parse($time_end)->diff($time_start)->format('%H-%i-%s');
        $time_data = explode('-', $time_data);
        return $time_data[0]*3600 + $time_data[1]*60 + $time_data[2];
    }
}
