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
            $table->string('version');
            $table->date('release_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_latest')->default(false);
            $table->string('download_url')->nullable();
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
