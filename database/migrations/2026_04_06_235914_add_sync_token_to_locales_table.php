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
        Schema::table('locales', function (Blueprint $table) {
            $table->string('sync_token', 32)->unique()->nullable()->after('telefono');
        });
        
        // Auto-generate tokens for existing locales
        foreach(\App\Models\Locale::all() as $locale) {
            $locale->sync_token = \Illuminate\Support\Str::random(32);
            $locale->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locales', function (Blueprint $table) {
            $table->dropColumn('sync_token');
        });
    }
};
