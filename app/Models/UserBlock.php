<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserBlock extends Model
{
    protected $fillable = ['blocker_user_id', 'blocked_user_id'];

    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }

    /**
     * Users hidden from the viewer in either direction: people they blocked and people who blocked them.
     */
    public static function hiddenUserIdsFor(?User $viewer): Collection
    {
        if (! $viewer) {
            return collect();
        }

        return static::where('blocker_user_id', $viewer->id)->pluck('blocked_user_id')
            ->merge(static::where('blocked_user_id', $viewer->id)->pluck('blocker_user_id'))
            ->unique()
            ->values();
    }

    public static function existsBetween(int $userId, int $otherUserId): bool
    {
        return static::query()
            ->where(fn ($query) => $query->where('blocker_user_id', $userId)->where('blocked_user_id', $otherUserId))
            ->orWhere(fn ($query) => $query->where('blocker_user_id', $otherUserId)->where('blocked_user_id', $userId))
            ->exists();
    }
}
