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
        Schema::create('academic_terms', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year', 9);
            $table->enum('semester', ['first', 'second', 'summer']);
            $table->date('start_date');
            $table->date('end_date');
            $table->date('start_enrollment');
            $table->date('end_enrollment');
            $table->date('start_mid_grade');
            $table->date('end_mid_grade');
            $table->date('start_final_grade');
            $table->date('end_final_grade');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_terms');
    }
};
