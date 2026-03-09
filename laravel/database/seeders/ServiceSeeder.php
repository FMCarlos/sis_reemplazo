<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            'Subdirección Gestión y Desarrollo de las Personas',
            'Subdirección Recursos Físicos y Financieros',
            'Subdirección Gestión Asistencial',
            'Dirección del Hospital',
            'S. Ginecología y Obstetricia',
            'U. de Calidad y Seguridad del Paciente',
            'U. Prevención y Control de IAAS',
            'U. OIRS y Participación Ciudadana',
            'U. Control de Gestión',
            'Oficina S. Social',
            'Oficina de Partes',
            'Secretaría Dirección',
            'Coordinación Enfermería',
            'Consultorio Especialidades Médicas',
            'Consultorio Especialidades Gineco-Obstétricas',
            'Consultorio Especialidades Odontológicas',
            'Centro de Salud Mental',
            'U. de Emergencia Hospitalaria',
            'S. Médico-Quirúrgico Adulto Cuidados Básicos',
            'S. Médico-Quirúrgico Adulto Cuidados Medios',
            'S. Médico Quirúrgico Infantil',
            'S. Pensionado',
            'U. de Paciente Crítico Adulto (UPC)',
            'U. Pabellón, Recuperación y Esterilización',
            'U. de Farmacia',
            'U. de Rehabilitación',
            'U. de Laboratorio',
            'U. de Procedimientos Endoscópicos',
            'U. Alimentación y Nutrición',
            'U. Imagenología',
            'Coordinación de Matronería',
            'U. GES',
            'U. Pre Quirúrgica',
            'U. Gestión de la Demanda (Ex SOME)',
            'U. de Gestión de Pacientes',
            'U. de Gestión de las Personas',
            'U. Capacitación y Relación Asistencial Docente (RAD)',
            'U. de Reclutamiento y Selección',
            'U. Prevención de Riesgos y Salud Ocupacional',
            'Sala Cuna, Jardín Infantil y Club Escolar.',
            'U. de Ambientes Laborales',
            'U. Bienestar',
            'U. Abastecimiento',
            'U. Contabilidad y Finanzas',
            'U. Informática',
            'U. Servicios Generales',
            'U. Equipamiento Industrial e Infraestructura',
            'U. Equipamiento Médico',
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['name' => $service],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}