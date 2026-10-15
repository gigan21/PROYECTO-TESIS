<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_race_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_log_id')->nullable()->constrained('learning_logs')->nullOnDelete();
            $table->string('classroom', 50)->nullable()->index();
            $table->string('session_id', 64)->unique();
            $table->unsignedTinyInteger('hits')->default(0);
            $table->unsignedTinyInteger('misses')->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->unsignedInteger('total_time_sec')->default(0);
            $table->boolean('exploded')->default(false);
            $table->boolean('completed')->default(false);
            $table->boolean('won')->default(false);
            $table->json('weak_areas')->nullable();
            $table->json('strong_areas')->nullable();
            $table->json('by_type_accuracy')->nullable();
            $table->json('by_graph_kind_accuracy')->nullable();
            $table->json('telemetry_json')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['classroom', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_race_sessions');
    }
};
