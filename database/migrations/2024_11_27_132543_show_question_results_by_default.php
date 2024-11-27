<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::table('polls', function (Blueprint $table) {
            $table->boolean('show_question_results')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('polls', function (Blueprint $table) {
            $table->boolean('show_question_results')->default(false)->change();
        });
    }
};
