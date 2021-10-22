<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UuidTrait;

class Session extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'active',
        'start_time',
        'finish_time',
        'time',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'type',
        'user_id',
        'slug',
        'phone',
        'email',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     *  Get the time when the user entered the room
     *
     *  @param string | $user_id
     *  @param string | $room_id
     *
     * @return object | $result
     */
    public function getStartTimeUser ()
    {
        $result = self::where(['room_id' => $this->room_id, 'user_id' => $this->user_id])
            ->orderBy('user_id', 'DESC')
            ->get();
        return $result;
    }

    /**
     *  Let's count the users in the room at the moment
     *
     * @param $room_id | string
     */
    public function countUser ($room_id, $start_time)
    {
        return self::where('room_id', $room_id)->get()->count();
    }
}
