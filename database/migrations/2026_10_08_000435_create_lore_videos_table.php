<?php
// database/migrations/2026_10_07_200000_create_lore_videos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lore_videos', function (Blueprint $table) {
            $table->id();
            $table->string('youtube_id', 20);
            $table->string('title')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lore_videos');
    }
};