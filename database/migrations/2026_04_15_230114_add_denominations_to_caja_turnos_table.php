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
        Schema::table('caja_turnos', function (Blueprint $table) {
            $table->json('denominaciones_apertura')->after('monto_apertura_real')->nullable();
            $table->json('denominaciones_cierre')->after('monto_cierre_real')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caja_turnos', function (Blueprint $table) {
            $table->dropColumn(['denominaciones_apertura', 'denominaciones_cierre']);
        });
    }
};
