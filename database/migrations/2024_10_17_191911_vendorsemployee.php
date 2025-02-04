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
        Schema::create('vendorsemployee', function (Blueprint $table) {
            $table->id('pbv_emp_id');
            $table->integer('pbv_emp_vid');
            $table->string('pbv_emp_firstname');
            $table->string('pbv_emp_lastname');
            $table->string('pbv_emp_gender');
            $table->string('pbv_emp_NIC');
            $table->string('pbv_emp_address');
            $table->string('pbv_emp_contactno');
            $table->string('pbv_emp_email');
            $table->integer('pbv_emp_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
