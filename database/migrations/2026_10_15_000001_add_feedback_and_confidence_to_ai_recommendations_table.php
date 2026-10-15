<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_recommendations', function (Blueprint $table) {
            $table->dropForeign(['topic_id']);
            $table->dropForeign(['recommended_content_id']);
        });

        Schema::table('ai_recommendations', function (Blueprint $table) {
            $table->unsignedBigInteger('topic_id')->nullable()->change();
            $table->unsignedBigInteger('recommended_content_id')->nullable()->change();
            $table->foreign('topic_id')->references('id')->on('topics')->nullOnDelete();
            $table->foreign('recommended_content_id')->references('id')->on('contents')->nullOnDelete();

            $table->decimal('confidence', 5, 4)->nullable()->after('reason');
            $table->string('model_version', 16)->default('v1')->after('confidence');
            $table->boolean('teacher_feedback')->nullable()->after('is_completed');
            $table->timestamp('teacher_feedback_at')->nullable()->after('teacher_feedback');
            $table->foreignId('teacher_feedback_by')->nullable()->after('teacher_feedback_at')
                ->constrained('users')->nullOnDelete();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('ai_recommendations', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropConstrainedForeignId('teacher_feedback_by');
            $table->dropColumn([
                'teacher_feedback_at',
                'teacher_feedback',
                'model_version',
                'confidence',
            ]);
            $table->dropForeign(['topic_id']);
            $table->dropForeign(['recommended_content_id']);
        });

        Schema::table('ai_recommendations', function (Blueprint $table) {
            $table->unsignedBigInteger('topic_id')->nullable(false)->change();
            $table->unsignedBigInteger('recommended_content_id')->nullable(false)->change();
            $table->foreign('topic_id')->references('id')->on('topics')->cascadeOnDelete();
            $table->foreign('recommended_content_id')->references('id')->on('contents')->cascadeOnDelete();
        });
    }
};
