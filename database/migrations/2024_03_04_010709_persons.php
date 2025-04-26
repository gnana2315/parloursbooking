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
        Schema::create('persons', function (Blueprint $table) {
            $table->id('pbp_id');
            $table->integer('pbv_id')->nullable();
            $table->string('pbp_intial')->nullable();
            $table->string('pbp_firstname')->nullable();
            $table->string('pbp_lastname')->nullable();
            $table->string('pbp_nicno')->nullable();
            $table->string('pbp_nic')->nullable();
            $table->string('pbp_contactno')->nullable();
            $table->string('pbp_email')->unique();
            $table->string('pbp_address')->nullable();
            $table->integer('pbp_city')->nullable();
            $table->integer('pbp_gender')->nullable();
            $table->integer('pbp_dob')->nullable();
            $table->integer('pbp_status')->nullable();
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
