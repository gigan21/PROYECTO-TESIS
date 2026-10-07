<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount'); // positivo = gana, negativo = gasta
            $table->string('source', 50); // 'crossword', 'projectiles', 'shop', 'admin', 'bonus'
            $table->string('description')->nullable();
            $table->integer('balance_after'); // snapshot del saldo tras la transacción
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};