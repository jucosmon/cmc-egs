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
        Schema::table('users', function (Blueprint $table) {
            $table->string('province_code', 10)->nullable()->after('address');
            $table->string('province_name', 100)->nullable()->after('province_code');
            $table->string('city_code', 10)->nullable()->after('province_name');
            $table->string('city_name', 100)->nullable()->after('city_code');
            $table->string('barangay_code', 10)->nullable()->after('city_name');
            $table->string('barangay_name', 100)->nullable()->after('barangay_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'province_code',
                'province_name',
                'city_code',
                'city_name',
                'barangay_code',
                'barangay_name',
            ]);
        });
    }
};
