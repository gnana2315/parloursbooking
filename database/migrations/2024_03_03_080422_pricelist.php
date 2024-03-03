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
        Schema::create('pricelist', function (Blueprint $table) {
            $table->id('pbpl_id');
            $table->integer('pbpl_vendorid');
            $table->integer('pbpl_serviceid');
            $table->string('pbpl_duration');
            $table->string('pbpl_price');
            $table->integer('pbsl_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('pricelist');
    }
};
