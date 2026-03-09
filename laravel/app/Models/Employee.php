<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'rut',
        'dv',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'full_name',
        'calidad_juridica',
        'legal_quality_id',
        'ley',
        'estamento',
        'estament_id',
        'profesion',
        'profession_id',
        'titulo_homologacion',
        'especialidad',
        'specialty_id',
        'unidad',
        'sub_unidad',
        'sub_unit_id',
        'horas_semanales',
        'cargo_jornada_turno',
        'nombre_jefatura',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'horas_semanales' => 'integer',
        ];
    }

    public function subUnit(): BelongsTo
    {
        return $this->belongsTo(SubUnit::class);
    }

    public function estament(): BelongsTo
    {
        return $this->belongsTo(Estament::class);
    }

    public function legalQuality(): BelongsTo
    {
        return $this->belongsTo(LegalQuality::class);
    }

    public function profession(): BelongsTo
    {
        return $this->belongsTo(Profession::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class);
    }

    public function subjectStaffingAssignments(): HasMany
    {
        return $this->hasMany(RequestStaffing::class, 'subject_employee_id');
    }

    public function replacementStaffingAssignments(): HasMany
    {
        return $this->hasMany(RequestStaffing::class, 'replacement_employee_id');
    }
}
