<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropColumn(['step_label', 'hint_formula']);

            $table->string('block_type', 20)->nullable()->after('option_text');
            $table->text('content')->nullable()->after('block_type');
            $table->string('unit')->nullable()->after('content');
            $table->decimal('tolerance', 8, 4)->nullable()->after('unit');
            $table->unsignedInteger('sort_order')->default(0)->after('tolerance');

            $table->index(['question_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('question_options', function (Blueprint $table) {
            $table->dropIndex(['question_id', 'sort_order']);

            $table->dropColumn([
                'block_type',
                'content',
                'unit',
                'tolerance',
                'sort_order',
            ]);

            $table->string('step_label')->nullable()->after('option_text');
            $table->string('hint_formula')->nullable()->after('step_label');
        });
    }
};
