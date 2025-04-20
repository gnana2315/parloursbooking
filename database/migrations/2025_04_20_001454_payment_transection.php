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
            $table->integer('pbpt_booking_id')->nullable();
            $table->float('pbpt_total_amount', 5, 2)->default(0.00);
            $table->float('pbpt_discount_amount', 5, 2)->default(0.00);
            $table->float('pbpt_final_amount', 5, 2)->default(0.00);
            $table->string('pbpt_payment_response')->nullable();
            $table->string('pbpt_payment_ref_no')->nullable();
            $table->string('pbpt_description')->nulable();
            $table->integer('pbpt_status');
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
