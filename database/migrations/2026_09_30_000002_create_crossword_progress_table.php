<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crossword_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('current_level')->default(1)->index();
            $table->unsignedTinyInteger('max_level_reached')->default(1)->index();
            $table->json('learned_words')->nullable();
            $table->unsignedInteger('total_correct_attempts')->default(0);
            $table->unsignedInteger('total_wrong_attempts')->default(0);
            $table->unsignedInteger('coins_earned')->default(0);
            $table->timestamp('last_played_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crossword_progress');
    }
};
