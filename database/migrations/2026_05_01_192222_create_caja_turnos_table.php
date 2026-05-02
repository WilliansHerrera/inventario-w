<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('caja_turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('monto_apertura_esperado', 15, 2)->default(0);
            $table->decimal('monto_apertura_real', 15, 2)->default(0);
            $table->decimal('diferencia_apertura', 15, 2)->default(0);
            $table->decimal('monto_cierre_esperado', 15, 2)->default(0);
            $table->decimal('monto_cierre_real', 15, 2)->default(0);
            $table->decimal('diferencia', 15, 2)->default(0);
            $table->timestamp('abierto_at')->nullable();
            $table->timestamp('cerrado_at')->nullable();
            $table->string('estado')->default('abierto');
            $table->json('denominaciones_apertura')->nullable();
            $table->json('denominaciones_cierre')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_turnos');
    }
};
