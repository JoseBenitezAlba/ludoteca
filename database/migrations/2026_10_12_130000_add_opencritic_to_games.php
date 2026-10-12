<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedSmallInteger('opencritic_score')->nullable();
            $table->string('opencritic_url', 300)->nullable();
            $table->timestamp('opencritic_checked_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['opencritic_score', 'opencritic_url', 'opencritic_checked_at']);
        });
    }
};
