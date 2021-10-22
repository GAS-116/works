<?php

namespace App\Models;

use App\Traits\OwnerTrait;
use App\Traits\UuidTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;
    use SoftDeletes;
    use UuidTrait;
    use OwnerTrait;

    /**
     * @var string[]
     */
    protected $fillable = [
        'name',
        'status',
        'owner_by',
        'password'
    ];

    protected $hidden = [
        'password',
        'created_at',
        'updated_at',
        'owner_by',
        'pivot',
        'deleted_at'
    ];

    /**
     * @return string[]
     */
    public static function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:50',
            'status' => 'nullable|boolean',
            'password' => 'nullable|string'
        ];
    }

    /**
     * Boot the model.
     *
     * @return  void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obj) {
            do {
                $slug = mb_strtolower(Str::random(3) . '-' . Str::random(3), 'UTF-8');
            } while (self::where('slug', $slug)->first());

            $obj->setAttribute('slug', $slug);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roomUsers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function histories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function session(): HasMany
    {
        return $this->HasMany(Session::class);
    }

    public static function getSlug ($uuid_room)
    {
        return self::select('slug')->where('id', $uuid_room)->first();
    }
}
