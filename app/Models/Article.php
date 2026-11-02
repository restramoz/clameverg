<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'image_brief',
        'seo_title',
        'meta_description',
        'target_city_slug',
        'target_district_slug',
        'service_slug',
        'faqs',
        'status',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'faqs' => 'array',
            'image_brief' => 'array',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Scope for published articles.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft articles.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if article is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
