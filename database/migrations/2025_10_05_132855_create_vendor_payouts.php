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
        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id('pbvp_id');
            $table->unsignedBigInteger('pbvp_vendor_id');
            $table->decimal('pbvp_total_earned', 10, 2)->default(0.00);
            $table->decimal('pbvp_total_paid', 10, 2)->default(0.00);
            $table->decimal('pbvp_total_due', 10, 2)->default(0.00);
            $table->integer('pbvp_status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payouts');
    }
};
