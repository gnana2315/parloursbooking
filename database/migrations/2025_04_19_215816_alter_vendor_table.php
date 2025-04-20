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
            $table->integer('pbv_service_type')->after('pbv_servicefor')->nullable();
            $table->integer('pbv_business_type')->after('pbv_service_type')->nullable();
            $table->string('pbv_display_name', 255)->after('pbv_name')->nullable();
            $table->string('pbv_longatitude')->after('pbv_city')->nullable();
            $table->string('pbv_latitude')->after('pbv_longatitude')->nullable();
            $table->integer('pbv_bankinfo_id')->after('pbv_latitude')->nullable();
            $table->integer('pbv_service_capacity')->after('pbv_bankinfo_id')->nullable();
            $table->string('pbv_short_description')->after('pbv_accept_terms')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor', function (Blueprint $table) {
            $table->dropColumn('pbv_service_type');
            $table->dropColumn('pbv_business_type');
            $table->dropColumn('pbv_longatitude');
            $table->dropColumn('pbv_latitude');
        });
    }
};
