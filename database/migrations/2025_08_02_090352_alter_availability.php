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
        Schema::table('vendor_standard_availability', function (Blueprint $table) {
            $table->enum('pbvsa_isEdit', [0, 1])->default(0)->after('pbvsa_is_open')
                ->comment('0 = No, 1 = Yes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_standard_availability', function (Blueprint $table) {
            $table->dropColumn('pbvsa_isEdit');
        });
    }
};
