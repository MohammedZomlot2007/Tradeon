<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizEvent extends Model
{
    use HasFactory;

    protected $table = 'biz_events';

    protected $fillable = [
        'company_id',
        'title',
        'event_type',
        'industry',
        'location',
        'country',
        'start_date',
        'end_date',
        'website_link',
        'description',
        'image',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
