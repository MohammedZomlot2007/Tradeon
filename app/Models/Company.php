<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'logo',
        'cover',
        'country',
        'city',
        'business_types',
        'industry',
        'company_size',
        'founded_year',
        'website',
        'description',
        'products_summary',
        'services_summary',
        'markets_served',
        'export_markets',
        'import_markets',
        'certifications',
        'production_capacity',
        'moq',
        'business_hours',
        'contact_options',
        'team_members',
        'verified_tier',
        'followers_count',
        'connections_count',
        'profile_views',
        'response_rate',
        'response_time',
        'is_featured',
    ];

    protected $casts = [
        'business_types' => 'array',
        'products_summary' => 'array',
        'services_summary' => 'array',
        'markets_served' => 'array',
        'export_markets' => 'array',
        'import_markets' => 'array',
        'certifications' => 'array',
        'contact_options' => 'array',
        'team_members' => 'array',
        'is_featured' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function factory(): HasOne
    {
        return $this->hasOne(Factory::class);
    }

    public function productShowcases(): HasMany
    {
        return $this->hasMany(ProductShowcase::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function businessRequests(): HasMany
    {
        return $this->hasMany(BusinessRequest::class);
    }

    public function mediaUploads(): HasMany
    {
        return $this->hasMany(MediaUpload::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(BizEvent::class);
    }
}
