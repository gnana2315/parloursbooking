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
        Schema::create('vendor_special_closes', function (Blueprint $table) {
            $table->id('pbvsc_id');
            $table->integer('pbvsc_vendor_id');
            $table->date('pbvsc_day')->nullable();
            $table->boolean('pbvsc_full_day_closed')->default(false);
            $table->time('pbvsc_from_time')->nullable();
            $table->time('pbvsc_to_time')->nullable();
            $table->integer('pbvsc_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_special_closes');
    }
};
