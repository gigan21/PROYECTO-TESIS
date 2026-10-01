<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crossword_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('crossword_word_id')->nullable()->constrained('crossword_words')->nullOnDelete();
            $table->unsignedTinyInteger('level')->index();
            $table->string('word_attempted', 30);
            $table->boolean('was_correct')->index();
            $table->unsignedInteger('time_spent_seconds')->nullable();
            $table->unsignedInteger('coins_awarded')->default(0);
            $table->unsignedInteger('xp_awarded')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crossword_events');
    }
};
