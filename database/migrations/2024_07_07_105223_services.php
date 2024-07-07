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
        Schema::create('services', function (Blueprint $table) {
            $table->id('pbs_id');
            $table->integer('pbs_vendor_id');
            $table->integer('pbs_servicefor_id');
            $table->integer('pbs_category_id');
            $table->string('pbs_name');
            $table->string('pbs_description');
            $table->float('pbs_charges');
            $table->integer('pbs_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('services');
    }
};
