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
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->enum('pbpc_promo_types', ['global', 'vendor', 'service', 'vendor_service']);
            $table->json('pbpc_vendor_ids')->nullable();
            $table->json('pbpc_service_ids')->nullable();
            $table->json('pbpc_vendor_service_map')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn('pbpc_promo_types');
            $table->dropColumn('pbpc_vendor_ids');
            $table->dropColumn('pbpc_service_ids');
            $table->dropColumn('pbpc_vendor_service_map');
        });
    }
};
