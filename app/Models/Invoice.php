<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $table = 'invoices';

    protected $fillable = [
        'user_id',
        'enterprise_id',
        'number',
        'issue_date',
        'value',
        'description',
        'nf_url',
    ];

    protected $appends = ['nf_url_full'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function getNfUrlFullAttribute()
    {
        return $this->nf_url ? asset('storage/' . $this->nf_url) : null;
    }
}
