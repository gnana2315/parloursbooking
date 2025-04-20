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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id('pbbd_id');
            $table->integer('pbbd_booking_id');
            $table->integer('pbbd_service_id');
            $table->integer('pbbd_employee_id')->nullable();
            $table->integer('pbbd_promo_id')->nullable();
            $table->float('pbbd_amount')->default(0);
            $table->float('pbbd_discount')->default(0);
            $table->float('pbbd_total_amount')->default(0);
            $table->string('pbb_promo_id')->nullable();
            $table->integer('pbb_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};
