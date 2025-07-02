<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enterprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'company_id',
        'state',
        'city',
        'address',
        'number',
        'contact',
        'social_media',
    ];
}
