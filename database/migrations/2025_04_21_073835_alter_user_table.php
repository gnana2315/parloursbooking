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
        Schema::table('users', function (Blueprint $table) {            
            $table->integer('pbu_mobileno')->after('pbu_email')->nullable();
            $table->integer('pbu_verification_token_expires_at')->after('pbu_email_verified_at')->nullable();
            $table->integer('pbu_mobileno_verified_at')->after('pbu_email_verified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {            
            $table->dropColumn('pbu_mobileno');
            $table->dropColumn('pbu_verification_token_expires_at');
            $table->dropColumn('pbu_mobileno_verified_at');
        });
    }
};
