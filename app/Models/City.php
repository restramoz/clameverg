<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'bps_code',
        'bps_province_code',
        'province',
        'island',
        'classification',
        'is_hub',
        'status',
    ];

    protected $casts = [
        'is_hub' => 'boolean',
    ];

    public function kecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class, 'city_id');
    }

    public function activeKecamatans(): HasMany
    {
        return $this->hasMany(Kecamatan::class, 'city_id')->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeHub($query)
    {
        return $query->where('is_hub', true);
    }

    public function scopeByProvince($query, string $province)
    {
        return $query->where('province', $province);
    }

    public function scopeByIsland($query, string $island)
    {
        return $query->where('island', $island);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
