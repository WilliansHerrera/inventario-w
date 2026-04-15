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
        Schema::table('cajas', function (Blueprint $table) {
            // Si la caja tiene apertura automática desde el POS, no pide arqueo manual
            $table->boolean('apertura_automatica_pos')->default(false)->after('incluir_en_apertura_global');
        });
    }

    public function down(): void
    {
        Schema::table('cajas', function (Blueprint $table) {
            $table->dropColumn('apertura_automatica_pos');
        });
    }
};
