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
            $table->boolean('win_auto_actualizar')->default(true);
            $table->string('win_min_version')->default('1.0.0');
            $table->boolean('win_auto_inicio')->default(true);
            $table->string('win_default_ruta_datos')->default('C:\POS\Data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['win_auto_actualizar', 'win_min_version', 'win_auto_inicio', 'win_default_ruta_datos']);
        });
    }
};
