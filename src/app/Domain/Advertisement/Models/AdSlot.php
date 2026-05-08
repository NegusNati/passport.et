<?php

namespace App\Domain\Advertisement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdSlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'page_context',
        'format',
        'desktop_width',
        'desktop_height',
        'mobile_width',
        'mobile_height',
        'is_active',
    ];

    protected $casts = [
        'desktop_width' => 'integer',
        'desktop_height' => 'integer',
        'mobile_width' => 'integer',
        'mobile_height' => 'integer',
        'is_active' => 'boolean',
    ];

    public function advertisements(): HasMany
    {
        return $this->hasMany(Advertisement::class, 'slot_code', 'code');
    }
}
