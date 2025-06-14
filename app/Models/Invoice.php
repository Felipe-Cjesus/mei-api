<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'user_id',
        'number',
        'issue_date',
        'value',
        'description',
        'nf_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
