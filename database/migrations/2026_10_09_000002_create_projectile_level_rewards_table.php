<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projectile_level_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 64);
            $table->unsignedTinyInteger('level');
            $table->boolean('finished')->default(false);
            $table->unsignedSmallInteger('coins_awarded')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['session_id', 'level', 'finished']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projectile_level_rewards');
    }
};
