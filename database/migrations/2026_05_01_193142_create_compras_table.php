<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedores')->onDelete('cascade');
            $table->foreignId('locale_id')->constrained('locales')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nro_documento')->nullable();
            $table->string('estado')->default('completado');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('impuesto_porcentaje', 5, 2)->default(0);
            $table->decimal('impuesto_monto', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
