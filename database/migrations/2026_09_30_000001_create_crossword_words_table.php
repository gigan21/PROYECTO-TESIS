<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crossword_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('topics')->nullOnDelete();
            $table->string('answer', 30)->index();
            $table->string('clue', 500);
            $table->enum('difficulty', ['facil', 'medio', 'dificil']);
            $table->unsignedTinyInteger('level')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['difficulty', 'level']);
            $table->index(['is_active', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crossword_words');
    }
};
