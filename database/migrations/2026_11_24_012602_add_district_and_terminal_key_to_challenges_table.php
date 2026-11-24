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
        Schema::table('challenges', function (Blueprint $table) {
            $table->string('district', 64)->nullable()->after('discipline')->index();
            $table->string('terminal_key', 100)->nullable()->after('map_trigger_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex(['district']);
            $table->dropIndex(['terminal_key']);
            $table->dropColumn(['district', 'terminal_key']);
        });
    }
};
