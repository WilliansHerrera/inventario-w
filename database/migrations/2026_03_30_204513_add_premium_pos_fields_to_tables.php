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
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'imagen')) {
                $table->string('imagen')->nullable()->after('descripcion');
            }
        });

        Schema::table('global_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('global_settings', 'receipt_header')) {
                $table->text('receipt_header')->nullable()->after('theme_palette');
            }
            if (!Schema::hasColumn('global_settings', 'receipt_footer')) {
                $table->text('receipt_footer')->nullable()->after('receipt_header');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });

        Schema::table('global_settings', function (Blueprint $table) {
            $table->dropColumn(['receipt_header', 'receipt_footer']);
        });
    }
};
