<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inventario: qué ítems tiene cada estudiante
        Schema::create('student_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_item_id')->constrained('shop_items')->cascadeOnDelete();
            $table->timestamp('obtained_at')->useCurrent();

            $table->unique(['user_id', 'shop_item_id']); // no puede tener el mismo ítem 2 veces
        });

        // 2. Campo para el banner equipado
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->foreignId('banner_id')
                ->nullable()
                ->after('coins')
                ->constrained('shop_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropForeign(['banner_id']);
            $table->dropColumn('banner_id');
        });

        Schema::dropIfExists('student_items');
    }
};