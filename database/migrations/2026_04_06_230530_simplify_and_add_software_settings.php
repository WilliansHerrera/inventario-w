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
            $table->dropColumn('impresora_termica');
            $table->boolean('auto_actualizar')->nullable();
            $table->string('version_requerida')->nullable();
            $table->boolean('auto_inicio')->nullable()->comment('Iniciar con Windows');
            $table->string('ruta_datos')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminal_pos', function (Blueprint $table) {
            $table->string('impresora_termica')->nullable();
            $table->dropColumn(['auto_actualizar', 'version_requerida', 'auto_inicio', 'ruta_datos']);
        });
    }
};
