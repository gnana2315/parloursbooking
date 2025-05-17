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
        Schema::create('servicetype', function (Blueprint $table) {
            $table->id('pbst_id');
            $table->integer('pbst_service_for')->nullable();
            $table->string('pbst_name');
            $table->string('pbst_icon')->nullable();
            $table->string('pbst_description')->nullable();
            $table->integer('pbst_status')->nullable()->default(1);
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicetype');
    }
};
