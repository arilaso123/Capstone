<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public const STATUS_WAITING_PAYMENT = 'Menunggu Pembayaran';
    public const STATUS_PAYMENT_VERIFIED = 'Pembayaran Diverifikasi';
    public const STATUS_PROCESSING = 'Diproses';
    public const STATUS_SHIPPED = 'Barang Dikirim';
    public const STATUS_COMPLETED = 'Selesai';
    public const STATUS_CANCELLED = 'Dibatalkan';

    public static array $statuses = [
        self::STATUS_WAITING_PAYMENT,
        self::STATUS_PAYMENT_VERIFIED,
        self::STATUS_PROCESSING,
        self::STATUS_SHIPPED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'order_number',
        'checkout_token',
        'customer_id',
        'po_period_id',
        'total_amount',
        'payment_deadline_at',
        'status',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'shipping_postal_code',
        'courier',
        'tracking_number',
        'shipping_cost',
        'customer_notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'payment_deadline_at' => 'datetime',
            'shipping_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order): void {
            if ($order->status === self::STATUS_WAITING_PAYMENT && !$order->payment_deadline_at) {
                $order->payment_deadline_at = now()->addDay();
            }
        });
    }

    public function paymentDeadlinePassed(): bool
    {
        return $this->payment_deadline_at !== null && $this->payment_deadline_at->isPast();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function poPeriod(): BelongsTo
    {
        return $this->belongsTo(PoPeriod::class);
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function cancellationRequest(): HasOne
    {
        return $this->hasOne(CancellationRequest::class)->latestOfMany();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_WAITING_PAYMENT,
            self::STATUS_PAYMENT_VERIFIED,
        ]);
    }

    /**
     * Statuses available to the operational fulfillment workflow.
     * Payment verification and cancellation have dedicated controllers.
     */
    public function nextOperationalStatuses(): array
    {
        return match ($this->status) {
            self::STATUS_PAYMENT_VERIFIED => [self::STATUS_PROCESSING],
            self::STATUS_PROCESSING => [self::STATUS_SHIPPED],
            self::STATUS_SHIPPED => [self::STATUS_COMPLETED],
            default => [],
        };
    }
}

