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
        Schema::table('enrolled_subjects', function (Blueprint $table) {
            // Practical subset of registrar codes for smaller institutions.
            $table->enum('final_grade_code', ['INC', 'INP', 'DRP', 'W', 'UD', 'FDA', 'P', 'AU'])
                ->nullable()
                ->after('final_grade');
            $table->date('completion_due_at')
                ->nullable()
                ->after('final_grade_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrolled_subjects', function (Blueprint $table) {
            $table->dropColumn(['final_grade_code', 'completion_due_at']);
        });
    }
};
