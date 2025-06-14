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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('pbn_id');
            $table->unsignedBigInteger('pbn_user_id'); // or customer_id/vendor_id if needed
            $table->string('pbn_title');
            $table->text('pbn_message');
            $table->boolean('pbn_is_read')->default(false); // unread by default
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
