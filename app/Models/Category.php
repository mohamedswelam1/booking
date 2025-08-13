<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasUlids, HasFactory;

    protected $fillable = [
        'name', 'slug',
    ];

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
