<?php

use App\Enums\FormSubmissionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_type_id')->constrained('form_types')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('status')->default(FormSubmissionStatus::DRAFT->value);
            $table->json('payload_json')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['form_type_id', 'status']);
            $table->index(['submitted_by', 'submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
    }
};
