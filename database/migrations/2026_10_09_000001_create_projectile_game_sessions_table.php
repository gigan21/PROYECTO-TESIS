<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projectile_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 64)->unique();
            $table->string('result', 10); // win | lose
            $table->unsignedTinyInteger('level_reached')->default(1);
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total_time_seconds')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->unsignedInteger('total_shots')->default(0);
            $table->unsignedInteger('total_hits')->default(0);
            $table->unsignedInteger('total_misses')->default(0);
            $table->string('classroom', 50)->nullable();
            $table->json('telemetry_json')->nullable();
            $table->json('analysis_text')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('classroom');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projectile_game_sessions');
    }
};
