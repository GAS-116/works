<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoryUser extends Model
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
        'room_id',
        'user_id',
        'time',
    ];

    public $timestamps = false;

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return [
            'room_id' => 'required|string',
            'user_id' => 'required|string',
            'time' => 'integer',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
