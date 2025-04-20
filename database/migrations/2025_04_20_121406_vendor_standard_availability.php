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
        Schema::create('vendor_standard_availability', function (Blueprint $table) {
            $table->id('pbvsa_id');
            $table->integer('pbvsa_vendor_id');
            $table->enum('pbvsa_day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])->nullable();
            $table->time('pbvsa_start_time')->nullable();
            $table->time('pbvsa_end_time')->nullable();
            $table->boolean('pbvsa_is_open')->default(false);
            $table->integer('pbvsa_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_standard_availability');
    }
};
