<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Factory extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'factory_type',
        'location_address',
        'production_lines',
        'annual_output',
        'machinery',
        'certifications',
        'photos',
        'videos',
        'manufacturing_process',
    ];

    protected $casts = [
        'machinery' => 'array',
        'certifications' => 'array',
        'photos' => 'array',
        'videos' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
