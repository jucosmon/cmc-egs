<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scheduled_subjects', function (Blueprint $table) {
            $table->timestamp('midterm_submitted_at')->nullable()->after('curriculum_subject_id');
            $table->timestamp('final_submitted_at')->nullable()->after('midterm_submitted_at');
            $table->foreignId('midterm_submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('final_submitted_at');
            $table->foreignId('final_submitted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('midterm_submitted_by');
        });
    }

    public function down(): void
    {
        Schema::table('scheduled_subjects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('midterm_submitted_by');
            $table->dropConstrainedForeignId('final_submitted_by');
            $table->dropColumn(['midterm_submitted_at', 'final_submitted_at']);
        });
    }
};
