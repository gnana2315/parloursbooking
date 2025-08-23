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
        Schema::table('vendor', function (Blueprint $table) {
            $table->string('pbv_display_name')->default(0)->after('pbv_business_name');
            $table->string('pbv_staff_count')->default(0)->after('pbv_accept_terms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor', function (Blueprint $table) {
            $table->dropColumn('pbv_display_name');
            $table->dropColumn('pbv_staff_count');
        });
    }
};
