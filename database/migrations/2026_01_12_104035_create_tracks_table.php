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
        Schema::create('tracks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('artist_name');
            $table->integer('price_cents'); // Prix en centimes (ex: 999 pour 9.99€)
            $table->string('preview_url');  // Lien public S3 (extrait 30s)
            $table->string('full_file_key'); // Clé privée S3 (fichier complet)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracks');
    }
};
