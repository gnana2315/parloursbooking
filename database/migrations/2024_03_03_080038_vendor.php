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
        Schema::create('vendor', function (Blueprint $table) {
            $table->id('pbv_id');   
            $table->integer('pbv_tenentid')->nullable();
            $table->integer('pbv_servicefor')->nullable();
            $table->integer('pbv_vendortype')->nullable();
            $table->integer('pbv_business_category')->nullable();
            $table->string('pbv_business_name')->nullable();
            $table->text('pbv_documents')->nullable()->comment('Add NIC,BR, Certificate, Address Proof, Exp letter and etc with document meta data');
            $table->string('pbv_brno')->nullable();
            $table->string('pbv_email')->nullable()->unique();
            $table->string('pbv_contactno')->nullable();
            $table->string('pbv_address')->nullable();
            $table->string('pbv_city')->nullable();
            $table->string('pbv_longatitude')->nullable();
            $table->string('pbv_latitude')->nullable();
            $table->integer('pbv_accept_terms')->nullable();
            $table->integer('pbv_status')->nullable();
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor');
    }
};
