<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kecamatan extends Model
{
    protected $fillable = [
        'city_id',
        'name',
        'slug',
        'bps_code',
        'status',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
