<?php

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
        Schema::create('enrolled_subjects', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['enrolled', 'dropped', 'completed'])->default('enrolled');
            $table->decimal('midterm_grade', 5, 2)->nullable();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->foreignId('enrollment_id')->constrained()->onDelete('cascade');
            $table->foreignId('scheduled_subject_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrolled_subjects');
    }
};
