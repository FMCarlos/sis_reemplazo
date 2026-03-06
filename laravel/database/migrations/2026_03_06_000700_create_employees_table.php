<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('rut');
            $table->string('dv', 1);
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');
            $table->string('full_name');
            $table->string('calidad_juridica');
            $table->string('ley');
            $table->string('estamento');
            $table->string('profesion');
            $table->string('titulo_homologacion')->nullable();
            $table->string('especialidad')->nullable();
            $table->string('unidad');
            $table->string('sub_unidad')->nullable();
            $table->unsignedInteger('horas_semanales')->nullable();
            $table->string('cargo_jornada_turno')->nullable();
            $table->string('nombre_jefatura')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['rut', 'dv']);
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
