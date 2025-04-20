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
        Schema::create('vendor_employees', function (Blueprint $table) {
            $table->id('pbve_id');
            $table->integer('pbve_type_id');
            $table->string('pbve_firstname');
            $table->string('pbve_lastname');
            $table->date('pbve_dob')->nullable();
            $table->string('pbve_sex');
            $table->string('pbve_nic')->nullable();
            $table->string('pbve_nic_no')->nullable();
            $table->string('pbve_contact_no')->nullable();
            $table->string('pbve_email')->nullable();
            $table->string('pbve_address')->nullable();
            $table->string('pbve_exp')->nullable();
            $table->integer('pbve_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_employees');
    }
};
