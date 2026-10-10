<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedItem extends Model
{
    use HasFactory;

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'item_id')->where('item_type', 'company');
    }

    protected $fillable = [
        'user_id',
        'item_type',
        'item_id',
        'collection_name',
    ];
}
