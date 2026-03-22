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
            // Update enum to only CMC-specific codes
            // DR - Dropped, NA - Never Attended, INC - Incomplete, W - Withdrawn, NG - No Grade
            $table->enum('final_grade_code', ['DR', 'NA', 'INC', 'W', 'NG'])
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrolled_subjects', function (Blueprint $table) {
            // Revert to previous enum values if needed
            $table->enum('final_grade_code', ['INC', 'INP', 'DRP', 'W', 'UD', 'FDA', 'P', 'AU'])
                ->nullable()
                ->change();
        });
    }
};
