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
        Schema::create('pb_users', function (Blueprint $table) {
            $table->id('pbu_id');
            $table->integer('pbu_usertype');
            $table->integer('pbu_personid');
            $table->string('pbu_name');
            $table->string('pbu_email')->unique();
            $table->timestamp('pbu_email_verified_at')->nullable();
            $table->string('pbu_password');
            $table->integer('pbu_status');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('pb_users');
    }
};
