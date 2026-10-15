<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('learning_logs', function (Blueprint $table) {
            $table->boolean('abandoned')->default(false)->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('learning_logs', function (Blueprint $table) {
            $table->dropColumn('abandoned');
        });
    }
};
