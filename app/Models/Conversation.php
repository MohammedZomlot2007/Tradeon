<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_one_id',
        'user_two_id',
        'company_one_id',
        'company_two_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function userOne(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_one_id');
    }

    public function userTwo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_two_id');
    }

    public function companyOne(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_one_id');
    }

    public function companyTwo(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_two_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
