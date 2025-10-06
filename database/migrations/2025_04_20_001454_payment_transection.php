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
        Schema::create('payment_transection', function (Blueprint $table) {
            $table->id('pbpt_id');
            $table->string('pbpt_transaction_id')->unique();
            $table->unsignedBigInteger('pbpt_booking_id')->nullable();
            $table->unsignedBigInteger('pbpt_vendor_id')->nullable();
            $table->unsignedBigInteger('pbpt_customer_id')->nullable();
            $table->string('pbpt_payment_method')->nullable();
            $table->float('pbpt_total_amount', 10, 2)->default(0.00);
            $table->float('pbpt_discount_amount', 10, 2)->default(0.00);
            $table->float('pbpt_final_amount', 10, 2)->default(0.00);
            $table->float('pbpt_platform_fee', 10, 2)->default(0.00);
            $table->float('pbpt_vendor_amount', 10, 2)->default(0.00);
            $table->string('pbpt_payment_response')->nullable();
            $table->string('pbpt_payment_ref_no')->nullable();
            $table->string('pbpt_description')->nulable();
            $table->integer('pbpt_status')->comment('Payment Status:0-Pending|1-Paid|2-Refunded|3-Declined');
            $table->text('pbpt_remarks')->nullable();
            $table->foreign('pbpt_booking_id')->references('pbb_id')->on('bookings')->onDelete('cascade');
            $table->foreign('pbpt_vendor_id')->references('pbc_id')->on('customer')->onDelete('cascade');
            $table->foreign('pbpt_customer_id')->references('pbv_id')->on('vendor')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transection');
    }
};
