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
        Schema::create('booking_refer', function (Blueprint $table) {
            $table->id('pbbr_id');
            $table->integer('pbbr_booking_id');
            $table->string('pbbr_person_name')->nullable();
            $table->string('pbbr_address')->nullable();
            $table->string('pbbr_contact_no')->nullable();
            $table->integer('pbbr_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_refer');
    }
};
