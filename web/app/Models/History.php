<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class History extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 1;
    const STATUS_PAUSED = 2;
    const STATUS_COMPLETED = 3;

    /**
     * @var int[]
     */
    public static array $statuses = [
        self::STATUS_ACTIVE,
        self::STATUS_PAUSED,
        self::STATUS_COMPLETED
    ];

    /**
     * @var string[]
     */
    protected $fillable = [
        'user_id',
        'room_id',
        'start_time',
        'finish_time'
    ];

    public $timestamps = false;

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return [
            'room_id' => 'required|string'
        ];
    }

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
}
