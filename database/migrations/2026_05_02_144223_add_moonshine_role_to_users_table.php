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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('moonshine_user_role_id')
                ->nullable()
                ->after('password')
                ->constrained('moonshine_user_roles')
                ->cascadeOnDelete();
            
            $table->string('avatar')->nullable()->after('name');
            $table->string('pos_pin', 4)->nullable()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['moonshine_user_role_id']);
            $table->dropColumn(['moonshine_user_role_id', 'avatar', 'pos_pin']);
        });
    }
};
