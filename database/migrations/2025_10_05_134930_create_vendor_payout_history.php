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
        Schema::create('vendor_payout_history', function (Blueprint $table) {
            $table->id('pbvph_id');
            $table->unsignedBigInteger('pbvph_payout_id');
            $table->unsignedBigInteger('pbvph_vendor_id');
            $table->decimal('pbvph_amount', 10, 2)->default(0.00);
            $table->string('pbvph_payment_method')->nullable();
            $table->string('pbvph_reference')->nullable();
            $table->string('pbvph_description')->nullable();
            $table->integer('pbvph_status')->default(0)->comments('0=pending,1=completed,2=failed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payout_history');
    }
};
