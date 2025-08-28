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
        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id('pbvd_id');
            $table->integer('pbvd_vendor_id');
            $table->integer('pbvd_required_document_id');
            $table->string('pbvd_document_name');
            $table->string('pbvd_document_url');
            $table->enum('pbvd_document_status', ['0', '1', '2', '3', '4'])->default(0)->comment('Document Status:NotUploaded|UnderReview|Pending|Accepted|Rejected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_documents');
    }
};
