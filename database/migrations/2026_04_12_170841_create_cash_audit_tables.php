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
        // 1. Categorías de Movimientos (Egresos/Ingresos)
        Schema::create('caja_movimiento_categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('tipo')->default('egreso'); // ingreso, egreso
            $table->boolean('es_sistema')->default(false); // Para categorías que no se pueden borrar
            $table->timestamps();
        });

        // 2. Tabla de Turnos de Caja
        Schema::create('caja_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            $table->decimal('monto_apertura', 12, 2)->default(0);
            $table->decimal('monto_cierre_esperado', 12, 2)->default(0);
            $table->decimal('monto_cierre_real', 12, 2)->nullable();
            $table->decimal('diferencia', 12, 2)->nullable();
            
            $table->timestamp('abierto_at')->nullable();
            $table->timestamp('cerrado_at')->nullable();
            $table->string('estado')->default('abierto'); // abierto, cerrado
            $table->timestamps();
        });

        // 3. Modificaciones en Cajas
        Schema::table('cajas', function (Blueprint $table) {
            $table->foreignId('turno_activo_id')->nullable()->constrained('caja_turnos')->onDelete('set null');
        });

        // 4. Modificaciones en Movimientos de Caja
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->foreignId('caja_turno_id')->nullable()->constrained('caja_turnos')->onDelete('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('caja_movimiento_categorias')->onDelete('set null');
        });

        // 5. Ajustes Globales
        Schema::table('global_settings', function (Blueprint $table) {
            $table->boolean('pos_block_without_shift')->default(false)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn('pos_block_without_shift');
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropForeign(['caja_turno_id']);
            $table->dropForeign(['categoria_id']);
            $table->dropColumn(['caja_turno_id', 'categoria_id']);
        });

        Schema::table('cajas', function (Blueprint $table) {
            $table->dropForeign(['turno_activo_id']);
            $table->dropColumn('turno_activo_id');
        });

        Schema::dropIfExists('caja_turnos');
        Schema::dropIfExists('caja_movimiento_categorias');
    }
};
