<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestAction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'request_id',
        'user_id',
        'action',
        'from_status',
        'to_status',
        'comment',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
