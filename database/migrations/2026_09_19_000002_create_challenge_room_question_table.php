<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_room_question', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['challenge_room_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_room_question');
    }
};
