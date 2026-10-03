<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('topics')->cascadeOnDelete();
            $table->string('game_type', 20);
            $table->unsignedInteger('total_time_seconds');
            $table->decimal('error_rate_percentage', 5, 2);
            $table->unsignedInteger('earned_xp')->default(0);
            $table->unsignedInteger('attempts')->default(1);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['topic_id', 'game_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_logs');
    }
};
