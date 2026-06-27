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
        Schema::create('payouts_batch_details', function (Blueprint $table) {
            $table->id('pbpbd_id');
            $table->bigInteger('pbpbi_btach_id');
            $table->bigInteger('pbpbi_vendor_payout_item_id');
            $table->string('pbpbi_status')->nullable()->comment('0-pending, 1-paid, 2-failed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts_batch_details');
    }
};
