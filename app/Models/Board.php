<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (Board $board) {
            if (blank($board->code)) {
                $board->code = static::generateCode($board->name);
            }
        });
    }

    /**
     * Build a short, unique board code from its name (e.g. "Sprint Release" -> "SPR").
     */
    protected static function generateCode(string $name): string
    {
        $base = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', $name) ?: 'BRD', 0, 3));
        $base = str_pad($base, 3, 'X');

        $code = $base;
        $i = 1;
        while (static::where('code', $code)->exists()) {
            $code = $base.(++$i);
        }

        return $code;
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }
}
