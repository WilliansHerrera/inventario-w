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
        Schema::create('pos_versions', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->text('changelog')->nullable();
            $table->string('filename')->nullable();
            $table->boolean('is_latest')->default(false);
            $table->timestamp('release_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_versions');
    }
};
