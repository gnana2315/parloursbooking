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
        Schema::create('business_type', function (Blueprint $table) {
            $table->id('pbbt_id');
            $table->string('pbbt_name')->nullable();
            $table->string('pbbt_icon')->nullable();
            $table->string('pbbt_description')->nullable();
            $table->integer('pbbt_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_type');
    }
};
