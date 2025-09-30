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
        Schema::create('cdrs_users', function (Blueprint $table) {
            // $table->id();
            $table->bigInteger('sf_id')->unique()->nullable();
            $table->string('sf_login')->unique()->nullable();
            $table->string('firstname')->nullable();
            $table->string('surname')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cdrs_users');
    }
};
