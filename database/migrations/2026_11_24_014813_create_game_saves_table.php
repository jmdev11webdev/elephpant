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
        Schema::create('game_saves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('slot');
            $table->string('label', 80)->nullable();
            $table->string('current_zone', 64)->default('foundations-hub');
            $table->integer('player_x')->default(12);
            $table->integer('player_y')->default(11);
            $table->string('facing', 16)->default('down');
            $table->unsignedInteger('playtime_seconds')->default(0);
            $table->timestamp('saved_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'slot']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_saves');
    }
};
