<?php

namespace App\Domain\Advertisement\Models;

use App\Domain\Advertisement\Data\AdvertisementSearchParams;
use App\Support\AdvertisementCrmFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advertisement extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'ad_slot_number',
        'ad_title',
        'ad_desc',
        'ad_excerpt',
        'ad_desktop_asset',
        'ad_mobile_asset',
        'ad_client_link',
        'status',
        'package_type',
        'ad_published_date',
        'ad_ending_date',
        'payment_status',
        'payment_amount',
        'client_name',
        'advertisement_request_id',
        'priority',
        'admin_notes',
    ];

    protected $casts = [
        'ad_published_date' => 'date',
        'ad_ending_date' => 'date',
        'payment_amount' => 'decimal:2',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
        'priority' => 'integer',
        'expiry_notification_sent' => 'boolean',
    ];

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_SCHEDULED = 'scheduled';

    // Payment status constants
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';
    public const PAYMENT_FAILED = 'failed';

    // Package type constants
    public const PACKAGE_WEEKLY = 'weekly';
    public const PACKAGE_MONTHLY = 'monthly';
    public const PACKAGE_YEARLY = 'yearly';

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ACTIVE,
            self::STATUS_PAUSED,
            self::STATUS_EXPIRED,
            self::STATUS_SCHEDULED,
        ];
    }

    public static function paymentStatuses(): array
    {
        return [
            self::PAYMENT_PENDING,
            self::PAYMENT_PAID,
            self::PAYMENT_REFUNDED,
            self::PAYMENT_FAILED,
        ];
    }

    public static function packageTypes(): array
    {
        return [
            self::PACKAGE_WEEKLY,
            self::PACKAGE_MONTHLY,
            self::PACKAGE_YEARLY,
        ];
    }

    // Relationships
    public function advertisementRequest()
    {
        return $this->belongsTo(AdvertisementRequest::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        $today = now()->toDateString();
        
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('ad_published_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('ad_ending_date')
                  ->orWhere('ad_ending_date', '>=', $today);
            });
    }

    public function scopeExpiringSoon(Builder $query, int $days = 3): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('ad_ending_date')
            ->whereBetween('ad_ending_date', [
                now()->startOfDay(),
                now()->addDays($days)->endOfDay()
            ])
            ->where('expiry_notification_sent', false);
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('ad_ending_date', '<', now()->toDateString())
            ->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_SCHEDULED]);
    }

    public function scopeBySlot(Builder $query, string $slotNumber): Builder
    {
        return $query->where('ad_slot_number', $slotNumber);
    }

    public function scopeFilter(Builder $query, AdvertisementSearchParams $params): Builder
    {
        $filters = $params->filters();

        if ($filters['ad_title']) {
            $query->where('ad_title', 'like', '%'.$filters['ad_title'].'%');
        }

        if ($filters['ad_slot_number']) {
            $query->where('ad_slot_number', $filters['ad_slot_number']);
        }

        if ($filters['client_name']) {
            $query->where('client_name', 'like', '%'.$filters['client_name'].'%');
        }

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['payment_status']) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if ($filters['package_type']) {
            $query->where('package_type', $filters['package_type']);
        }

        if ($filters['published_after']) {
            $query->whereDate('ad_published_date', '>=', $filters['published_after']);
        }

        if ($filters['published_before']) {
            $query->whereDate('ad_published_date', '<=', $filters['published_before']);
        }

        if ($filters['ending_after']) {
            $query->whereDate('ad_ending_date', '>=', $filters['ending_after']);
        }

        if ($filters['ending_before']) {
            $query->whereDate('ad_ending_date', '<=', $filters['ending_before']);
        }

        return $query;
    }

    public function scopeSort(Builder $query, AdvertisementSearchParams $params): Builder
    {
        [$column, $direction] = $params->sort();

        if (! in_array($column, AdvertisementCrmFilters::sortableColumns(), true)) {
            [$column, $direction] = AdvertisementCrmFilters::defaultSort();
        }

        return $query->orderBy($column, $direction);
    }

    public function scopeLimitForSearch(Builder $query, AdvertisementSearchParams $params): Builder
    {
        $limit = $params->limit() ?? AdvertisementCrmFilters::defaultLimit();
        return $query->limit($limit);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ad_published_date <= now()
            && ($this->ad_ending_date === null || $this->ad_ending_date >= now());
    }

    public function isExpired(): bool
    {
        return $this->ad_ending_date && $this->ad_ending_date < now();
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->ad_ending_date) {
            return null;
        }

        return now()->startOfDay()->diffInDays($this->ad_ending_date, false);
    }

    public function incrementImpressions(): void
    {
        $this->increment('impressions_count');
    }

    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    public function markExpiryNotificationSent(): void
    {
        $this->expiry_notification_sent = true;
        $this->save();
    }
}
