<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelOrder extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'order_id',
        'applicant_name',
        'destination',
        'departure_date',
        'return_date',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'departure_date' => 'date',
        'return_date' => 'date',
    ];

    /**
     * Status constants for travel orders
     */
    const STATUS_REQUESTED = 'solicitado';
    const STATUS_APPROVED = 'aprovado';
    const STATUS_CANCELLED = 'cancelado';

    /**
     * Get available status options with their labels
     *
     * @return array<string, string>
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_REQUESTED => 'Solicitado',
            self::STATUS_APPROVED => 'Aprovado',
            self::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    /**
     * Get the user that owns the travel order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the travel order can be cancelled
     * Business rule: Cannot cancel approved travel orders
     *
     * @return bool
     */
    public function canBeCancelled(): bool
    {
        return $this->status !== self::STATUS_APPROVED;
    }

    /**
     * Check if the travel order can be updated
     * Business rule: Cannot update approved or cancelled orders
     *
     * @return bool
     */
    public function canBeUpdated(): bool
    {
        return !in_array($this->status, [self::STATUS_APPROVED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope a query to only include travel orders with specific status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include travel orders within date range
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('departure_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include travel orders for specific destination
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $destination
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDestination($query, $destination)
    {
        return $query->where('destination', 'like', "%{$destination}%");
    }
}