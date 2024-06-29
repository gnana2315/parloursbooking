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
        Schema::create('seo_key_words', function (Blueprint $table) {
            $table->id('pbseo_id');
            $table->string('pbseo_page');
            $table->string('pbseo_words');
            $table->integer('pbseo_status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('seo_key_words');
    }
};
