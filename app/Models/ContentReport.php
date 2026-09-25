<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentReport extends Model
{
    public const REASONS = [
        'spam' => 'Spam atau promosi',
        'harassment' => 'Pelecehan atau ujaran kebencian',
        'inappropriate' => 'Konten tidak pantas',
        'privacy' => 'Melanggar privasi',
        'other' => 'Lainnya',
    ];

    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'invitation_id',
        'comment_id',
        'reason',
        'note',
        'status',
        'admin_note',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function invitation()
    {
        return $this->belongsTo(Invitation::class);
    }

    public function comment()
    {
        return $this->belongsTo(InvitationComment::class, 'comment_id');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }
}
