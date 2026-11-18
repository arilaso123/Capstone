<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends Model
{
    use HasFactory;

    public const STATUS_PENDING = Payment::STATUS_PENDING;
    public const STATUS_VALID = Payment::STATUS_VALID;
    public const STATUS_REJECTED = Payment::STATUS_REJECTED;
    public const STATUS_SUPERSEDED = 'Digantikan';

    protected $fillable = [
        'payment_id',
        'attempt_number',
        'amount',
        'proof_file_path',
        'verification_status',
        'admin_notes',
        'uploaded_at',
        'verified_by',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'amount' => 'decimal:2',
            'uploaded_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
