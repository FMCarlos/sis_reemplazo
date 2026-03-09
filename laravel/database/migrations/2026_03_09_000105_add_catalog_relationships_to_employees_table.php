<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->foreignId('sub_unit_id')->nullable()->after('sub_unidad')->constrained('sub_units')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('estament_id')->nullable()->after('estamento')->constrained('estaments')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('legal_quality_id')->nullable()->after('calidad_juridica')->constrained('legal_qualities')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('profession_id')->nullable()->after('profesion')->constrained('professions')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('specialty_id')->nullable()->after('especialidad')->constrained('specialties')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_unit_id');
            $table->dropConstrainedForeignId('estament_id');
            $table->dropConstrainedForeignId('legal_quality_id');
            $table->dropConstrainedForeignId('profession_id');
            $table->dropConstrainedForeignId('specialty_id');
        });
    }
};
