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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('locale_id')->constrained('locales')->onDelete('cascade');
            $table->decimal('saldo', 15, 2)->default(0);
            $table->boolean('abierta')->default(false);
            $table->boolean('incluir_en_apertura_global')->default(true);
            $table->boolean('apertura_automatica_pos')->default(false);
            $table->unsignedBigInteger('turno_activo_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
