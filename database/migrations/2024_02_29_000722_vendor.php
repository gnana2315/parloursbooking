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
        Schema::create('pb_vendor', function (Blueprint $table) {
            $table->id('pbv_id');
            $table->integer('pbv_servicetype');
            $table->string('pbv_name');
            $table->string('pbv_brno');
            $table->string('pbv_brdoc');
            $table->string('pbv_email')->unique();
            $table->string('pbv_contactno');
            $table->string('pbv_address');
            $table->integer('pbp_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('pb_vendor');
    }
};
