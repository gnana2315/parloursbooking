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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id('pbpc_id');
            $table->string('pbpc_name')->nullable();
            $table->string('pbpc_code')->nullable();
            $table->string('pbpc_discount_type')->nullable();
            $table->float('pbpc_value', 5, 2)->default(0.00);
            $table->decimal('pbpc_discount', 5, 2)->default(0.00);
            $table->float('pbpc_max_discount', 5, 2)->default(0.00);
            $table->date('pbpc_max_start_date')->nullable();
            $table->date('pbpc_max_end_date')->nullable();
            $table->float('pbpc_min_booking_amount')->default(0.00);
            $table->integer('pbpc_uses_count')->default(0);
            $table->string('pbpc_description')->nulable();
            $table->integer('pbpc_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });   
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
