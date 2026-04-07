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
        Schema::create('terminal_pos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->foreignId('caja_id')->constrained('cajas')->onDelete('cascade');
            $table->string('uuid')->unique()->nullable()->comment('Hardware Device ID');
            $table->string('impresora_termica')->nullable();
            $table->string('ip_remota')->nullable();
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_pos');
    }
};
