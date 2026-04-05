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
        Schema::table('vendor_payout_items', function (Blueprint $table) {
            $table->string('pbvpi_vendor_id')->nullable()->after('pbvpi_payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_payout_items', function (Blueprint $table) {
            $table->dropColumn('pbvpi_vendor_id');
        });
    }
};
