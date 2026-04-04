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
        Schema::table('payment_transection', function (Blueprint $table) {
            $table->decimal('pbpt_platform_discount_amount', 8, 2)->nullable()->after('pbpt_discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transection', function (Blueprint $table) {
            $table->dropColumn('pbpt_platform_discount_amount');
        });
    }
};
