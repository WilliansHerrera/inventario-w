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
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('win_kiosk_mode')->default(false);
            $table->boolean('win_debug_mode')->default(false);
            $table->integer('win_sync_interval')->default(60)->comment('In seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['win_kiosk_mode', 'win_debug_mode', 'win_sync_interval']);
        });
    }
};
