<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestStaffing extends Model
{
    use HasFactory;

    protected $table = 'request_staffing';

    protected $fillable = [
        'request_id',
        'subject_employee_id',
        'replacement_employee_id',
        'replacement_is_external',
        'replacement_rut',
        'replacement_dv',
        'replacement_full_name',
        'replacement_profession',
        'replacement_specialty',
        'replacement_notes',
        'absence_type_id',
        'absence_detail',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'replacement_is_external' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function subjectEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'subject_employee_id');
    }

    public function replacementEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'replacement_employee_id');
    }

    public function absenceType(): BelongsTo
    {
        return $this->belongsTo(AbsenceType::class);
    }
}
