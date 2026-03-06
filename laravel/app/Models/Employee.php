<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'ley',
        'estamento',
        'profesion',
        'titulo_homologacion',
        'especialidad',
        'unidad',
        'sub_unidad',
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

    public function subjectStaffingAssignments(): HasMany
    {
        return $this->hasMany(RequestStaffing::class, 'subject_employee_id');
    }

    public function replacementStaffingAssignments(): HasMany
    {
        return $this->hasMany(RequestStaffing::class, 'replacement_employee_id');
    }
}
