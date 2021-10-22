<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Services\DataService;

class HistoryMeeting extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'room_id',
        'start_time',
        'finish_time',
        'total_time',
    ];

    public $timestamps = false;

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return [
            'room_id' => 'required|string',
            'total_time' => 'integer',
            'start_time' => 'required|date',
            'finish_time' => 'date',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }


    public function updateRoomForFinishMeeting ($room_start_time)
    {
        $this->update([
            'finish_time' => Carbon::now()->toDateTimeString(),
            'total_time' => DataService::getTimeDifference($room_start_time, Carbon::now()->toDateTimeString()),
        ]);

        return $this;
    }
}
