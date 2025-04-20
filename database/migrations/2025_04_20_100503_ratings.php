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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id('pbr_id');
            $table->integer('pbr_vendor_id');
            $table->integer('pbr_booking_id');
            $table->integer('pbr_customer_id');
            $table->integer('rating')->nullable();
            $table->string('pbr_comments')->nullable();
            $table->integer('pbr_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
