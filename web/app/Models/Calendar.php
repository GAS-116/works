<?php

namespace App\Models;

use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Calendar extends Model
{
    use HasFactory;
    use UuidTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'title',
        'note',
        'password',
        'participants',
        'is_lobby',
        'live_stream',
        'start_date',
        'end_date',
        'user_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'participants' => 'json'
    ];

    /**
     * @return string[]
     */
    public static function rules()
    {
        return [
            'title' => 'required|string',
            'participants' => 'required|array',
            'start_date' => 'required|date',
            'note' => 'string',
            'is_lobby' => 'boolean',
            'live_stream' => 'string',
            'end_date' => 'date',
            'password' => 'string',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
