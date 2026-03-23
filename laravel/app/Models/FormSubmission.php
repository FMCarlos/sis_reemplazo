<?php

namespace App\Models;

use App\Enums\FormSubmissionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_type_id',
        'submitted_by',
        'status',
        'payload_json',
        'pdf_path',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => FormSubmissionStatus::class,
            'payload_json' => 'array',
            'submitted_at' => 'datetime',
        ];
    }

    public function formType(): BelongsTo
    {
        return $this->belongsTo(FormType::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(FormSubmissionAction::class);
    }
}
