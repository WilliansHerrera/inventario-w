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
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('moonshine_users')->onDelete('cascade');
        });

        Schema::table('caja_movimientos', function (Blueprint $table) {
            if (Schema::hasColumn('caja_movimientos', 'user_id')) {
                // Not all movements might have a FK yet depending on past migrations
                try { $table->dropForeign(['user_id']); } catch(\Exception $e) {}
                $table->foreign('user_id')->references('id')->on('moonshine_users')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caja_movimientos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('caja_turnos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
