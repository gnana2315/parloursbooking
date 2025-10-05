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
        Schema::create('vendor_payout_items', function (Blueprint $table) {
            $table->id('pbvpi_id');
            $table->unsignedBigInteger('pbvpi_payout_id');
            $table->unsignedBigInteger('pbvpi_booking_id');
            $table->unsignedBigInteger('pbvpi_payment_id');
            $table->decimal('pbvpi_amount', 10, 2)->default(0.00);
            $table->decimal('pbvpi_platform_fee', 10, 2)->default(0.00);
            $table->decimal('pbvpi_vendor_amount', 10, 2)->default(0.00);
            $table->integer('pbvpi_status')->default(0);
            $table->unsignedBigInteger('pbvpi_payout_history_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payout_items');
    }
};
