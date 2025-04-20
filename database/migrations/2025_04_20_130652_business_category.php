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
        Schema::create('business_category', function (Blueprint $table) {
            $table->id('pbbc_id');
            $table->integer('pbbc_vendor_id');
            $table->string('pbbc_name')->nullable();
            $table->string('pbbc_description')->nullable();
            $table->string('pbbc_image')->nullable();
            $table->boolean('pbbc_status')->default(1);
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_category');
    }
};
