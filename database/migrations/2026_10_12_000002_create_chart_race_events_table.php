<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_race_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 64);
            $table->unsignedTinyInteger('round_number');
            $table->string('question_type', 20);
            $table->string('graph_kind', 10);
            $table->string('graph_type', 32);
            $table->string('topic', 120);
            $table->boolean('correct')->default(false);
            $table->unsignedSmallInteger('answered_in_seconds')->default(0);
            $table->boolean('timed_out')->default(false);
            $table->string('wrong_answer', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['session_id', 'round_number']);
            $table->index(['user_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_race_events');
    }
};
