<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('posts') && ! Schema::hasColumn('posts', 'share_count')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->integer('share_count')->default(0)->after('comment_count');
            });
        }

        if (! Schema::hasTable('post_likes')) {
            Schema::create('post_likes', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['post_id', 'user_id']);
            });
        }

        if (! Schema::hasTable('post_shares')) {
            Schema::create('post_shares', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
                $table->string('comment', 500)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('admin_actions')) {
            Schema::create('admin_actions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action_type', 100);
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('reports')) {
            Schema::create('reports', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('reported_entity_type', 30);
                $table->unsignedBigInteger('reported_entity_id');
                $table->string('reason', 100);
                $table->text('additional_notes')->nullable();
                $table->string('status', 20)->default('pending');
                $table->timestamps();

                $table->index(['reported_entity_type', 'reported_entity_id']);
                $table->index(['status', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('admin_actions');
        Schema::dropIfExists('post_shares');
        Schema::dropIfExists('post_likes');

        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'share_count')) {
            Schema::table('posts', function (Blueprint $table): void {
                $table->dropColumn('share_count');
            });
        }
    }
};
