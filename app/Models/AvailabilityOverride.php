<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailabilityOverride extends Model
{
    use HasUlids, HasFactory;

    protected $fillable = [
        'provider_id', 'date', 'start_time', 'end_time', 'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'date' => 'date',
    ];

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }
}
