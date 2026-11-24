<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('discipline', 32)->index();
            $table->string('category');
            $table->string('difficulty', 32)->index();
            $table->string('evaluation_type', 32);
            $table->json('supported_languages');
            $table->unsignedInteger('xp_reward')->default(100);
            $table->unsignedInteger('estimated_minutes')->default(10);
            $table->string('skill_key', 64)->index();
            $table->string('map_trigger_id')->nullable()->index();
            $table->text('prompt_markdown');
            $table->json('starter_files');
            $table->json('hints');
            $table->json('decision_options')->nullable();
            $table->string('correct_decision_id')->nullable();
            $table->longText('public_tests');
            $table->longText('hidden_tests');
            $table->timestamps();
        });

        Schema::create('challenge_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 16)->default('submit');
            $table->string('language', 32)->default('php');
            $table->json('files_json');
            $table->string('decision_choice')->nullable();
            $table->string('status', 24)->index();
            $table->unsignedInteger('passed_tests')->default(0);
            $table->unsignedInteger('total_tests')->default(0);
            $table->unsignedInteger('execution_ms')->default(0);
            $table->longText('stdout')->nullable();
            $table->longText('stderr')->nullable();
            $table->json('results_json')->nullable();
            $table->unsignedInteger('xp_awarded')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'challenge_id', 'status']);
        });

        Schema::create('user_discipline_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('discipline', 32);
            $table->unsignedInteger('xp')->default(0);
            $table->string('rank', 32)->default('Novice');
            $table->unsignedInteger('completed_count')->default(0);
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('best_streak')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'discipline']);
        });

        Schema::create('user_skill_proficiencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('skill_key', 64);
            $table->unsignedTinyInteger('proficiency')->default(0);
            $table->unsignedInteger('completed_count')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'skill_key']);
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('badge_key', 64);
            $table->timestamp('unlocked_at');
            $table->timestamps();

            $table->unique(['user_id', 'badge_key']);
        });

        Schema::create('user_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title_key', 64);
            $table->boolean('equipped')->default(false);
            $table->timestamp('unlocked_at');
            $table->timestamps();

            $table->unique(['user_id', 'title_key']);
        });

        Schema::create('user_world_triggers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('trigger_id', 100);
            $table->foreignId('challenge_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('unlocked_at');
            $table->timestamps();

            $table->unique(['user_id', 'trigger_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_world_triggers');
        Schema::dropIfExists('user_titles');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('user_skill_proficiencies');
        Schema::dropIfExists('user_discipline_progress');
        Schema::dropIfExists('challenge_submissions');
        Schema::dropIfExists('challenges');
    }
};
