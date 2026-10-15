<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_feature_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('classroom', 20)->nullable()->index();
            $table->date('snapshot_date');
            $table->json('features_json');
            $table->string('features_version', 10)->default('v1');
            $table->timestamps();

            $table->unique(['user_id', 'snapshot_date']);
            $table->index(['classroom', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_feature_snapshots');
    }
};
