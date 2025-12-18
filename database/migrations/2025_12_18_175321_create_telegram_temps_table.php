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
        Schema::create('telegram_temps', function (Blueprint $table) {
            $table->id();
            $table->string('token')->index(); // This will store the NIS or unique token
            $table->string('chat_id');
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_temps');
    }
};
