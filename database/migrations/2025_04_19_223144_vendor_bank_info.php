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
        Schema::create('vendor_bank_info', function (Blueprint $table) {
            $table->id('pbvb_id')->nullable();
            $table->integer('pbvb_vendorid')->nullable();
            $table->string('pbvb_bankname')->nullable();
            $table->string('pbvb_branch')->nullable();
            $table->string('pbvb_accountno')->nullable();
            $table->integer('pbvb_status')->nullable();
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_bank_info');
    }
};
