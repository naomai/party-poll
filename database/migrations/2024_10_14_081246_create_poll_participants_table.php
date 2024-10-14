<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('poll_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained();
            $table->foreignId('user_id')->constrained();

            $table->unique(['poll_id', 'user_id']);

            /** Modify questions, edit name, publish poll */
            $table->boolean('can_modify_poll')->default(false);
            /** Start new question in `wait_for_everybody` mode */
            $table->boolean('can_control_flow')->default(false);
            /** See all responses live during voting */
            $table->boolean('can_see_progress')->default(false);
            /** Place answers */
            $table->boolean('can_answer')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('poll_participants');
    }
};
