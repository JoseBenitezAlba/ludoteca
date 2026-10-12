<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rawg_id')->unique();
            $table->string('slug')->unique();
            $table->string('name')->index();
            $table->string('image', 500)->nullable();
            $table->date('released')->nullable();
            $table->json('genres')->nullable();
            $table->json('platforms')->nullable();
            $table->json('developers')->nullable();
            $table->unsignedSmallInteger('metacritic')->nullable();
            $table->decimal('rawg_rating', 3, 2)->nullable();
            $table->unsignedInteger('ratings_count')->default(0);
            $table->unsignedSmallInteger('playtime')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('detailed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20);
            $table->unsignedTinyInteger('rating')->nullable(); // 1-10 = 0,5 a 5 estrellas
            $table->decimal('hours', 6, 1)->nullable();
            $table->string('platform', 60)->nullable();
            $table->text('review')->nullable();
            $table->date('finished_at')->nullable();
            $table->boolean('favorite')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'game_id']);
            $table->index(['game_id', 'rating']);
        });

        Schema::create('game_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('description', 240)->nullable();
            $table->timestamps();
        });

        Schema::create('game_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['game_list_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_list_items');
        Schema::dropIfExists('game_lists');
        Schema::dropIfExists('entries');
        Schema::dropIfExists('games');
    }
};
