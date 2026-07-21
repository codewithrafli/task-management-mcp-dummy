<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'board_id',
        'assignee_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'position',
    ];

    protected static function booted(): void
    {
        static::creating(function (Task $task) {
            if (blank($task->code)) {
                $prefix = Board::find($task->board_id)?->code ?? 'TASK';
                $seq = static::where('board_id', $task->board_id)->count() + 1;

                do {
                    $code = $prefix.'-'.$seq;
                    $seq++;
                } while (static::where('code', $code)->exists());

                $task->code = $code;
            }
        });
    }

    /**
     * Resolve a task by its human code (e.g. "SPR-1") or its numeric id.
     */
    public static function resolveRef(int|string $ref): ?self
    {
        if (ctype_digit((string) $ref)) {
            return static::find((int) $ref);
        }

        return static::where('code', Str::upper((string) $ref))->first();
    }

    protected function casts(): array
    {
        return [
            'status' => TaskStatus::class,
            'priority' => TaskPriority::class,
            'due_date' => 'date',
            'position' => 'integer',
        ];
    }

    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->where('status', '!=', TaskStatus::Done->value);
    }

    public function scopeStatus(Builder $query, TaskStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
