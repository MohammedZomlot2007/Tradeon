<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductShowcase extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'description',
        'industry',
        'category',
        'specifications',
        'moq',
        'available_markets',
        'images',
        'lead_time',
    ];

    protected $casts = [
        'specifications' => 'array',
        'available_markets' => 'array',
        'images' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
