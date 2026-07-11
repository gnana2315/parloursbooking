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
            $table->id('pbpbi_id');
            $table->bigInteger('pbpbi_batch_id');
            $table->bigInteger('pbpbi_vendor_id');
            $table->bigInteger('pbpbi_vendor_payout_item_id');
            $table->date('pbpbi_paid_date')->nullable();
            $table->string('pbpbi_paid_ref_no')->nullable();
            $table->string('pbpbi_paid_by')->nullable();
            $table->string('pbpbi_paid_slip_url')->nullable();
            $table->string('pbpbi_remarks')->nullable();
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
