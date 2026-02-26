<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->string('motivo')->nullable()->after('status');
            $table->date('fecha_inicio')->nullable()->after('motivo');
            $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            $table->string('nombre_reemplazo')->nullable()->after('fecha_fin');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['motivo', 'fecha_inicio', 'fecha_fin', 'nombre_reemplazo']);
        });
    }
};
