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
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('pos_block_without_shift')->default(false);
            $table->decimal('default_opening_amount', 15, 2)->default(0);
            $table->boolean('auto_open_shifts')->default(false);
            $table->string('cash_management_mode')->default('simple');
            $table->string('country_name')->nullable();
            $table->string('locale')->default('es');
            $table->string('currency_code')->default('USD');
            $table->string('currency_symbol')->default('$');
            $table->decimal('iva_porcentaje', 5, 2)->default(0);
            $table->boolean('prices_include_tax')->default(true);
            $table->string('theme_palette')->default('indigo');
            $table->text('receipt_header')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->decimal('margen_defecto', 5, 2)->default(0);
            $table->boolean('win_kiosk_mode')->default(false);
            $table->boolean('win_debug_mode')->default(false);
            $table->integer('win_sync_interval')->default(300);
            $table->boolean('win_auto_actualizar')->default(true);
            $table->string('win_min_version')->default('1.0.0');
            $table->boolean('win_auto_inicio')->default(true);
            $table->string('win_default_ruta_datos')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
