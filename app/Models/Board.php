<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
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

    /**
     * Resolve a board by its human code (e.g. "SPR") or its numeric id.
     */
    public static function resolveRef(int|string $ref): ?self
    {
        if (ctype_digit((string) $ref)) {
            return static::find((int) $ref);
        }

        return static::where('code', Str::upper((string) $ref))->first();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Everyone who can be assigned tasks on this board: the owner plus members.
     *
     * @return Collection<int, User>
     */
    public function team(): Collection
    {
        // collect() clones the relation so we never mutate the cached members.
        return collect($this->members->all())
            ->push($this->user)
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public function hasAccess(User $user): bool
    {
        return $this->user_id === $user->id
            || $this->members()->whereKey($user->id)->exists();
    }

    /**
     * Ids of everyone a task on this board may be assigned to (owner + members).
     *
     * @return array<int, int>
     */
    public function teamIds(): array
    {
        return $this->members()->pluck('users.id')->push($this->user_id)->unique()->all();
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Boards a user owns or is a member of.
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('members', fn (Builder $m) => $m->whereKey($user->id));
        });
    }
}
