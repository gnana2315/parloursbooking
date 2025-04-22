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
        Schema::table('persons', function (Blueprint $table) {
            $table->integer('pbp_city')->after('pbp_address')->nullable();
            $table->integer('pbp_gender')->after('pbp_city')->nullable();
            $table->integer('pbp_dob')->after('pbp_gender')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('pbp_city');
            $table->dropColumn('pbp_gender');
            $table->dropColumn('pbp_dob');
        });
    }
};
