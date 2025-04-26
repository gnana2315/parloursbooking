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
        Schema::create('vendor_type', function (Blueprint $table) {
            $table->id('pbvt_id');
            $table->string('pbvt_name')->nullable();
            $table->string('pbvt_icon')->nullable();
            $table->string('pbvt_description')->nullable();
            $table->integer('pbvt_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_type');
    }
};
