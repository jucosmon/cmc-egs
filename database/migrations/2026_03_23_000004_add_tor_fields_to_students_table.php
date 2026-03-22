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
        Schema::table('students', function (Blueprint $table) {
            $table->string('birth_place', 150)->nullable()->after('user_id');
            $table->string('religion', 100)->nullable()->after('birth_place');
            $table->string('citizenship', 100)->nullable()->after('religion');
            $table->string('father_name', 150)->nullable()->after('citizenship');
            $table->string('mother_name', 150)->nullable()->after('father_name');
            $table->string('elementary_school', 200)->nullable()->after('mother_name');
            $table->string('elementary_year', 30)->nullable()->after('elementary_school');
            $table->string('secondary_school', 200)->nullable()->after('elementary_year');
            $table->string('secondary_year', 30)->nullable()->after('secondary_school');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'birth_place',
                'religion',
                'citizenship',
                'father_name',
                'mother_name',
                'elementary_school',
                'elementary_year',
                'secondary_school',
                'secondary_year',
            ]);
        });
    }
};
