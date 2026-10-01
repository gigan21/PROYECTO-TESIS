<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('classroom', 20);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('session_date');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('status', 20)->default('activa');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'classroom']);
            $table->index(['classroom', 'session_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
