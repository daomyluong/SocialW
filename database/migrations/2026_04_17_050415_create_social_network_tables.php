<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Bảng media
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_user_id')->nullable();
            $table->enum('type', ['image', 'video', 'other']);
            $table->string('url', 1000);
            $table->string('filename', 255)->nullable();
            $table->string('mime', 100)->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // 2. Bổ sung cột social cho bảng users mặc định
        if (! Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username', 50)->unique()->nullable()->after('id');
            });
        }

        if (! Schema::hasColumn('users', 'display_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('display_name', 100)->nullable()->after('name');
            });
        }

        if (! Schema::hasColumn('users', 'bio')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('bio')->nullable()->after('password');
            });
        }

        if (! Schema::hasColumn('users', 'avatar_url')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('avatar_url', 1000)->nullable()->after('bio');
            });
        }

        if (! Schema::hasColumn('users', 'post_count')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('post_count')->default(0)->after('avatar_url');
            });
        }

        if (! Schema::hasColumn('users', 'follower_count')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('follower_count')->default(0)->after('post_count');
            });
        }

        if (! Schema::hasColumn('users', 'following_count')) {
            Schema::table('users', function (Blueprint $table) {
                $table->integer('following_count')->default(0)->after('follower_count');
            });
        }

        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('following_count');
            });
        }

        // 3. Bảng posts
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content')->nullable();
            $table->foreignId('media_id')->nullable()->constrained('media');
            $table->integer('like_count')->default(0);
            $table->integer('comment_count')->default(0);
            $table->enum('visibility', ['public', 'follower', 'private'])->default('public');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // 4. Bảng comments
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        // 5. Bảng followers
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followers');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('posts');
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'username',
                    'display_name',
                    'bio',
                    'avatar_url',
                    'post_count',
                    'follower_count',
                    'following_count',
                    'is_active',
                ]);
            });
        }
        Schema::dropIfExists('media');
    }
};