<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('polls', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('users');

            $table->text('title');

            /**
             * Current question in `wait_for_everybody` mode
             */
            $table->integer('sequence_id')->nullable();
            /**
             * Last published question, block modification and reordering
             */
            $table->integer('published_sequence_id')->nullable();

            
            $table->text('access_link_token')->nullable();
            $table->boolean('enable_link_invite')->default(true);
            /** After poll start, joining users can only observe */
            $table->boolean('close_after_start')->default(true);
            
            /**
             * Serve a question and wait for everybody 
             * vs
             * Let people answer entire poll independently
             */
            $table->boolean('wait_for_everybody')->default(true);
            /**
             * Enable going back and editing answers 
             * (disabled for wait_for_everybody=true)
             */
            $table->boolean('enable_revise_response')->default(false);
 
            /** After a question, show percentages/list of responses (anonymous) */
            $table->boolean('show_question_results')->default(false);
            /** Show user's avatar/name next to responses */
            $table->boolean('show_question_answers')->default(false);
            /** Same as above, but after entire poll (summary) */
            $table->boolean('show_end_results')->default(true);
            $table->boolean('show_end_answers')->default(false);
           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('polls');
    }
};
