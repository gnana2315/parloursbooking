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
        Schema::table('vendor_bank_info', function (Blueprint $table) { 
            $table->string('pbvb_holder_name')->default(0)->after('pbvb_bankname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_bank_info', function (Blueprint $table) {
            $table->dropColumn('pbvb_holder_name');
        });
    }
};
