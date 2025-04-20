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
        Schema::create('service_for', function (Blueprint $table) {
            $table->id('pbsf_id');
            $table->string('pbsf_name');
            $table->string('pbsf_icon')->nullable();
            $table->string('pbsf_description')->nullable();
            $table->integer('pbsf_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_for');
    }
};
