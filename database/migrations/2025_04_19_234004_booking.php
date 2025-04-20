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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('pbb_id');
            $table->integer('pbb_vendor_id');
            $table->string('pbb_customer_id');
            $table->string('pbb_promo_id')->nullable();
            $table->string('pbb_booking_details')->nullable();
            $table->date('pbb_booking_date')->nullable();
            $table->string('pbb_ref_no');
            $table->string('pbb_timeslot')->nullable();
            $table->string('pbb_type')->nullable();
            $table->string('pbb_service_location')->nullable();
            $table->string('pbb_contact_no')->nullable();
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
        Schema::dropIfExists('bookings');
    }
};
