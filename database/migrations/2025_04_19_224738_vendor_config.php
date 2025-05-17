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
        Schema::create('vendor_config', function (Blueprint $table) {
            $table->id('pbvc_id');
            $table->integer('pbvc_vendorid')->nullable();
            $table->string('pbvc_display_name')->nullable();
            $table->string('pbvc_logo')->nullable();
            $table->integer('pbvc_service_at_time')->nullable();
            $table->integer('pbvc_status')->nullable();
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_config');
    }
};
