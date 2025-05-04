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
        Schema::create('customer', function (Blueprint $table) {
            $table->id('pbc_id');
            $table->integer('pbc_user_id');
            $table->string('pbc_initial')->nullable();
            $table->string('pbc_first_name')->nullable();
            $table->string('pbc_last_name')->nullable();
            $table->date('pbc_dob')->nullable();
            $table->string('pbc_nic_no')->nullable();
            $table->string('pbc_nic_document')->nullable();
            $table->string('pbc_sex')->nullable();
            $table->string('pbc_address')->nullable();
            $table->string('pbc_city')->nullable();
            $table->string('pbc_email')->nullable();
            $table->string('pbc_contact_no')->nullable();
            $table->integer('pbc_accept_terms')->nullable();
            $table->integer('pbc_status');
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
