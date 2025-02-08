<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Verknüpft mit der Users-Tabelle
            $table->string('name'); // Original Dateiname
            $table->string('path'); // Pfad zur Datei
            $table->string('type'); // MIME-Typ
            $table->integer('size'); // Dateigröße in Bytes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};