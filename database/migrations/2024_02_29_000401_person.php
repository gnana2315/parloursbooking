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
        Schema::create('pb_persons', function (Blueprint $table) {
            $table->id('pbp_id');
            $table->string('pbp_intial');
            $table->string('pbp_firstname');
            $table->string('pbp_lastname');
            $table->string('pbp_nicno');
            $table->string('pbp_contactno');
            $table->string('pbp_address');
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
        Schema::drop('pb_persons');
    }
};
