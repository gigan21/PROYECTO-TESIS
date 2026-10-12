<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cure_level_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id', 64);
            $table->unsignedTinyInteger('level');
            $table->unsignedInteger('coins_awarded')->default(0);
            $table->unsignedInteger('xp_awarded')->default(0);
            $table->timestamps();

            $table->unique(['session_id', 'level']);
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('cure_game_sessions', function (Blueprint $table) {
            $table->foreignId('learning_log_id')
                ->nullable()
                ->after('user_id')
                ->constrained('learning_logs')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cure_game_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('learning_log_id');
        });

        Schema::dropIfExists('cure_level_rewards');
    }
};
