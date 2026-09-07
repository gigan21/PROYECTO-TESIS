<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('student_profiles', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('classroom', 10); // Agregado: para guardar '4A', '4B', '4C'
        $table->string('nickname')->nullable(); // Agregado: apodo opcional del estudiante
        $table->string('avatar_name')->default('default_avatar.png'); // Agregado: avatar predeterminado
        $table->unsignedInteger('xp_points')->default(0);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
