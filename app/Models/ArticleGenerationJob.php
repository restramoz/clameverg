<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleGenerationJob extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'article_generation_jobs';

    protected $fillable = [
        'user_id',
        'status',
        'result',
        'error',
    ];

    protected $casts = [
        'result' => 'array',
    ];
}