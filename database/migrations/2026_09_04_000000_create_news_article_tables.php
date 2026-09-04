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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->text('title');
            $table->string('slug', 190)->unique();
            $table->text('excerpt');
            $table->json('body');
            $table->string('cover_image_name', 190)->nullable();
            $table->unsignedInteger('cover_image_width')->nullable();
            $table->unsignedInteger('cover_image_height')->nullable();
            $table->boolean('is_visible')->default(false);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->index('is_visible');
        });

        Schema::create('article_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->string('image_name', 190);
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['article_id', 'sort_order']);
        });

        Schema::create('article_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->text('youtube_url');
            $table->string('youtube_id', 32);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['article_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_videos');
        Schema::dropIfExists('article_images');
        Schema::dropIfExists('articles');
    }
};
