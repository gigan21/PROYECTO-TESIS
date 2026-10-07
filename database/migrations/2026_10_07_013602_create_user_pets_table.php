<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('pet_id', 50);           // 'dog', 'cat', 'fox'
            $table->timestamp('obtained_at')->useCurrent();

            $table->unique(['user_id', 'pet_id']);  // no puede tener la misma 2 veces
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_pets');
    }
};