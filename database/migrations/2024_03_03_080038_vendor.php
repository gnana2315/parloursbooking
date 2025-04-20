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
        Schema::create('vendor', function (Blueprint $table) {
            $table->id('pbv_id');   
            $table->integer('pbv_servicefor');
            $table->integer('pbv_businesstype');
            $table->integer('pbv_business_category')->nullable();
            $table->string('pbv_business_name');
            $table->string('pbv_parlourcertificate')->nullable();
            $table->string('pbv_brno')->nullable();
            $table->string('pbv_brdoc')->nullable();
            $table->string('pbv_police_report')->nullable();
            $table->string('pbv_email')->unique();
            $table->string('pbv_contactno');
            $table->string('pbv_address');
            $table->string('pbv_city');
            $table->string('pbv_longatitude')->nullable();
            $table->string('pbv_latitude')->nullable();
            $table->integer('pbv_accept_terms');
            $table->integer('pbv_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor');
    }
};
