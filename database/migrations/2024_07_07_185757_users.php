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
        Schema::create('users', function (Blueprint $table) {
            $table->id('pbu_id');
            $table->integer('pbu_usertype');
            $table->integer('pbu_vid')->nullable();
            $table->integer('pbu_personid')->nullable();
            $table->string('pbu_name');
            $table->string('pbu_email')->unique()->nullable();
            $table->integer('pbu_mobileno')->nullable();
            $table->string('pbu_verification_token')->nullable();
            $table->timestamp('pbu_verification_token_expires_at')->nullable();
            $table->timestamp('pbu_email_verified_at')->nullable();
            $table->timestamp('pbu_mobileno_verified_at')->nullable();
            $table->string('password')->nullable();            
            $table->string('pbu_first_name')->nullable();
            $table->string('pbu_last_name')->nullable();
            $table->date('pbu_dob')->nullable();
            $table->integer('pbu_gender')->nullable();
            $table->string('pbu_address')->nullable();
            $table->string('pbu_city')->nullable();
            $table->string('pbu_accept_terms')->nullable();
            $table->integer('pbu_status')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};