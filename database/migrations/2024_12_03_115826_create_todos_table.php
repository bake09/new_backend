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
        Schema::create('todos', function (Blueprint $table) {
            // $table->id();
            $table->uuid('id')->primary();
            $table->foreignIdFor(App\Models\User::class);
            $table->string('content');
            $table->boolean('done')->default(false);
            $table->timestamp('due_date')->default(now()->addDay(1));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('todos');
    }
};
