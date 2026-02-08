<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrolled_subject_id')->constrained()->onDelete('cascade');
            $table->string('grade_period', 10);
            $table->decimal('old_grade', 5, 2)->nullable();
            $table->decimal('new_grade', 5, 2)->nullable();
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->foreignId('modified_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_change_logs');
    }
};
