<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('post_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('assigned_moderator_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('post_id')
                ->nullable()
                ->constrained('posts')
                ->cascadeOnDelete();
            $table->foreignId('category_id')
                ->constrained('post_categories')
                ->cascadeOnDelete();
            $table->string('cover');
            $table->string('title');
            $table->string('description');
            $table->text('content');
            $table->smallInteger('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_versions');
    }
};
