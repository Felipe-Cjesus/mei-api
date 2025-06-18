<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DasPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reference',
        'due_date',
        'payment_date',
        'amount',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}