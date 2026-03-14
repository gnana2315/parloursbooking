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
        Schema::table('vendor_documents', function (Blueprint $table) {
            $table->string('pbvd_document_extra')->nullable()->after('pbvd_document_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_documents', function (Blueprint $table) {
            $table->dropColumn('pbvd_document_extra');
        });
    }
};
