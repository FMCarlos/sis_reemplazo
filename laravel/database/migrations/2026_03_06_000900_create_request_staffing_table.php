<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_staffing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->unique()->constrained('requests')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('subject_employee_id')->constrained('employees')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('replacement_employee_id')->nullable()->constrained('employees')->cascadeOnUpdate()->nullOnDelete();
            $table->boolean('replacement_is_external')->default(false);
            $table->string('replacement_rut')->nullable();
            $table->string('replacement_dv', 1)->nullable();
            $table->string('replacement_full_name')->nullable();
            $table->string('replacement_profession')->nullable();
            $table->string('replacement_specialty')->nullable();
            $table->text('replacement_notes')->nullable();
            $table->foreignId('absence_type_id')->nullable()->constrained('absence_types')->cascadeOnUpdate()->nullOnDelete();
            $table->text('absence_detail')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_staffing');
    }
};
