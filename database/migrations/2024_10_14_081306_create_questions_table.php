<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users');
            $table->foreignId('poll_id')->constrained();

            $table->integer("poll_sequence_id");

            $table->unique(['poll_id', 'poll_sequence_id']);

            $table->text('question');
            /** range, rating, select, input */
            $table->string('type', 16);
            $table->json('response_params');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('questions');
    }
};
