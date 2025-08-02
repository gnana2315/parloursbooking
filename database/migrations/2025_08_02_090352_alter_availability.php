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
            $table->enum('pbvsa_isEdit', ['0', '1']);
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
