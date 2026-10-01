<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('classroom');
            $table->string('title');
            $table->string('code', 12)->unique();
            $table->string('status')->default('waiting');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index('classroom');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_rooms');
    }
};
