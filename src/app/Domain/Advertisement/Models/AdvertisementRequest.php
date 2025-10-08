<?php

namespace App\Domain\Advertisement\Models;

use App\Domain\Advertisement\Data\AdvertisementRequestSearchParams;
use App\Support\AdvertisementFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdvertisementRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'phone_number',
        'email',
        'full_name',
        'company_name',
        'description',
        'file_path',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_APPROVED = 'approved';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONTACTED,
            self::STATUS_REJECTED,
            self::STATUS_APPROVED,
        ];
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isContacted(): bool
    {
        return $this->status === self::STATUS_CONTACTED;
    }

    public function scopeFilter(Builder $query, AdvertisementRequestSearchParams $params): Builder
    {
        $filters = $params->filters();

        if (!empty($filters['full_name'])) {
            $query->where('full_name', 'like', '%'.$filters['full_name'].'%');
        }

        if (!empty($filters['company_name'])) {
            $query->where('company_name', 'like', '%'.$filters['company_name'].'%');
        }

        if (!empty($filters['phone_number'])) {
            $query->where('phone_number', 'like', $filters['phone_number'].'%');
        }

        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%'.$filters['email'].'%');
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['created_after'])) {
            $query->whereDate('created_at', '>=', $filters['created_after']);
        }

        if (!empty($filters['created_before'])) {
            $query->whereDate('created_at', '<=', $filters['created_before']);
        }

        return $query;
    }

    public function scopeSort(Builder $query, AdvertisementRequestSearchParams $params): Builder
    {
        [$column, $direction] = $params->sort();

        $sortableColumns = ['full_name', 'company_name', 'phone_number', 'email', 'status', 'created_at', 'contacted_at'];

        if (! in_array($column, $sortableColumns, true)) {
            $column = 'created_at';
            $direction = 'desc';
        }

        return $query->orderBy($column, $direction);
    }

    public function scopeLimitForSearch(Builder $query, AdvertisementRequestSearchParams $params): Builder
    {
        $limit = $params->limit() ?? AdvertisementFilters::defaultLimit();
        return $query->limit($limit);
    }
}
