<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();          // "banner-nebulosa-carmesi"
            $table->text('description')->nullable();

            $table->string('category', 30);            // banner | avatar | badge | pet
            $table->string('rarity', 20)->default('common'); // common | rare | epic | legendary

            $table->integer('price')->default(0);

            $table->string('media_type', 20)->default('image'); // image | video
            $table->string('media_path');                        // images/banners/xxx.webp

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_items');
    }
};