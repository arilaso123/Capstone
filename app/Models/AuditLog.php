<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'event', 'subject_type', 'subject_id', 'before_state', 'after_state', 'ip_address', 'user_agent', 'created_at',
    ];

    protected function casts(): array
    {
        return ['before_state' => 'array', 'after_state' => 'array', 'created_at' => 'datetime'];
    }

    public static function record(string $event, Model $subject, ?array $before, ?array $after): self
    {
        return static::create([
            'actor_id' => Auth::id(),
            'event' => $event,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'before_state' => $before,
            'after_state' => $after,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'created_at' => now(),
        ]);
    }
}
