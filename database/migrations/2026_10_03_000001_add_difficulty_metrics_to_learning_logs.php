<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_logs', function (Blueprint $table) {
            $table->unsignedInteger('easy_attempts')->default(0)->after('skipped_attempts');
            $table->unsignedInteger('easy_errors')->default(0)->after('easy_attempts');
            $table->unsignedInteger('easy_skips')->default(0)->after('easy_errors');
            $table->unsignedInteger('easy_time_seconds')->default(0)->after('easy_skips');

            $table->unsignedInteger('medium_attempts')->default(0)->after('easy_time_seconds');
            $table->unsignedInteger('medium_errors')->default(0)->after('medium_attempts');
            $table->unsignedInteger('medium_skips')->default(0)->after('medium_errors');
            $table->unsignedInteger('medium_time_seconds')->default(0)->after('medium_skips');

            $table->unsignedInteger('hard_attempts')->default(0)->after('medium_time_seconds');
            $table->unsignedInteger('hard_errors')->default(0)->after('hard_attempts');
            $table->unsignedInteger('hard_skips')->default(0)->after('hard_errors');
            $table->unsignedInteger('hard_time_seconds')->default(0)->after('hard_skips');
        });
    }

    public function down(): void
    {
        Schema::table('learning_logs', function (Blueprint $table) {
            $table->dropColumn([
                'easy_attempts', 'easy_errors', 'easy_skips', 'easy_time_seconds',
                'medium_attempts', 'medium_errors', 'medium_skips', 'medium_time_seconds',
                'hard_attempts', 'hard_errors', 'hard_skips', 'hard_time_seconds',
            ]);
        });
    }
};
