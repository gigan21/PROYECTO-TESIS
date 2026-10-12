<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cure_game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('classroom', 50)->nullable()->index();
            $table->string('session_id', 64)->unique();
            $table->unsignedTinyInteger('level_reached')->default(1);
            $table->unsignedTinyInteger('niveles_completados')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedInteger('hits')->default(0);
            $table->unsignedInteger('misses')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->unsignedInteger('score')->default(0);
            $table->string('rank', 80)->nullable();
            $table->unsignedInteger('max_streak')->default(0);
            $table->boolean('won')->default(false);
            $table->unsignedInteger('pista_usadas')->default(0);
            $table->unsignedInteger('total_time_sec')->default(0);
            $table->json('formulas_mastered')->nullable();
            $table->json('formulas_review')->nullable();
            $table->json('errors_by_type')->nullable();
            $table->json('levels_json')->nullable();
            $table->json('telemetry_json')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['classroom', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cure_game_sessions');
    }
};
