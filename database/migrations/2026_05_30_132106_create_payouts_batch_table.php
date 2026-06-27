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
        Schema::create('payouts_batch', function (Blueprint $table) {
            $table->id('pbpb_id');
            $table->string('pbpb_batch_no')->unique();
            $table->string('pbpb_batch_name')->nullable();
            $table->decimal('pbpb_total_amount', 10, 2)->default(0);
            $table->integer('pbpb_total_payouts')->default(0);
            $table->date('pbpb_payout_date')->nullable();
            $table->date('pbpb_batch_valid_date')->nullable();
            $table->text('pbpb_notes')->nullable();
            $table->string('pbpb_paid_ref_no')->nullable();
            $table->string('pbpb_paid_by')->nullable();
            $table->string('pbpb_paid_slip_url')->nullable();
            $table->bigInteger(('pbpb_status'))->default(0)->comment('0: Pending, 1: Paid, 2: Failed');
            $table->string('pbpb_remarks')->nullable();
            $table->bigInteger('pbpb_created_by')->nullable();
            $table->bigInteger('pbpb_updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts_batch');
    }
};
