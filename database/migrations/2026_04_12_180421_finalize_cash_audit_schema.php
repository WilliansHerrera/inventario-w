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
            // Renombrar si existe el nombre antiguo
            if (Schema::hasColumn('caja_turnos', 'monto_apertura') && !Schema::hasColumn('caja_turnos', 'monto_apertura_real')) {
                $table->renameColumn('monto_apertura', 'monto_apertura_real');
            }
            
            // Añadir campos si no existen
            if (!Schema::hasColumn('caja_turnos', 'monto_apertura_esperado')) {
                $table->decimal('monto_apertura_esperado', 12, 2)->default(0)->after('user_id');
            }

            if (!Schema::hasColumn('caja_turnos', 'diferencia_apertura')) {
                $table->decimal('diferencia_apertura', 12, 2)->default(0)->after('monto_apertura_real');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caja_turnos', function (Blueprint $table) {
            // Revenir cambios si es necesario
        });
    }
};
