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
        Schema::table('terminal_pos', function (Blueprint $table) {
            $table->boolean('kiosk_mode')->nullable();
            $table->boolean('debug_mode')->nullable();
            $table->integer('sync_interval')->nullable()->comment('Override, in seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminal_pos', function (Blueprint $table) {
            $table->dropColumn(['kiosk_mode', 'debug_mode', 'sync_interval']);
        });
    }
};
