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
            $table->integer('pbs_service_type');
            $table->integer('pbs_service_for');
            $table->string('pbs_name');
            $table->text('pbs_description')->nullable();
            $table->integer('pbs_duration_cetegory')->nullable();
            $table->integer('pbs_duration')->default(0);
            $table->string('pbs_image')->nullable();
            $table->float('pbs_price', 6, 2)->default(0.00);
            $table->string('pbs_employees')->nullable();
            $table->boolean('pbs_status')->default(1);
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
