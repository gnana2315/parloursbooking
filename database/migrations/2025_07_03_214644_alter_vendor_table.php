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
        Schema::table('vendor', function (Blueprint $table) {
            $table->string('pbv_first_name')->nullable();
            $table->string('pbv_last_name')->nullable();
            $table->string('pbv_gender')->nullable();
            $table->date('pbv_dob')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor', function (Blueprint $table) {
            $table->dropColumn('pbv_first_name');
            $table->dropColumn('pbv_last_name');
            $table->dropColumn('pbv_gender');
            $table->dropColumn('pbv_dob');
        });
    }
};
