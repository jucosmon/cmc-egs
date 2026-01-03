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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('code', 20)->unique();
            $table->enum('degree_type', ['bachelors', 'masters', 'doctors', 'associate']);
            $table->integer('total_units');
            $table->integer('duration_years');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('program_head_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
