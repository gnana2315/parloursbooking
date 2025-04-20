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
        Schema::create('vendor_services', function (Blueprint $table) {
            $table->id('pbvs_id');
            $table->integer('pbvs_vendor_id');
            $table->string('pbvs_service_type');
            $table->string('pbvs_service_id');
            $table->string('pbvs_frequency')->nullable();
            $table->integer('pbs_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_services');
    }
};
