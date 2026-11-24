<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'enterprise_id',
        'description',
        'amount',
        'date',
        'type',
        'document_path',
    ];

    protected $appends = ['document_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enterprise(): BelongsTo
    {
        return $this->belongsTo(Enterprise::class);
    }

    public function getDocumentUrlAttribute()
    {
        return $this->document_path ? asset('storage/' . $this->document_path) : null;
    }
}
