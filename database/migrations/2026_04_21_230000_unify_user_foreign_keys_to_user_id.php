<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'author_user_id') && ! Schema::hasColumn('posts', 'user_id')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropForeign(['author_user_id']);
            });

            DB::statement('ALTER TABLE posts CHANGE author_user_id user_id BIGINT UNSIGNED NOT NULL');

            Schema::table('posts', function (Blueprint $table): void {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('bookmarks3') && Schema::hasColumn('bookmarks3', 'user_id')) {
            Schema::table('bookmarks3', function (Blueprint $table): void {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('stories') && Schema::hasColumn('stories', 'user_id')) {
            DB::statement('ALTER TABLE stories MODIFY user_id BIGINT UNSIGNED NOT NULL');

            Schema::table('stories', function (Blueprint $table): void {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stories') && Schema::hasColumn('stories', 'user_id')) {
            Schema::table('stories', function (Blueprint $table): void {
                $table->dropForeign(['user_id']);
            });

            DB::statement('ALTER TABLE stories MODIFY user_id INT NOT NULL');
        }

        if (Schema::hasTable('bookmarks3') && Schema::hasColumn('bookmarks3', 'user_id')) {
            Schema::table('bookmarks3', function (Blueprint $table): void {
                $table->dropForeign(['user_id']);
            });
        }

        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'user_id')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropForeign(['user_id']);
            });

            DB::statement('ALTER TABLE posts CHANGE user_id author_user_id BIGINT UNSIGNED NOT NULL');

            Schema::table('posts', function (Blueprint $table): void {
                $table->foreign('author_user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};