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
    Schema::create('notifications', function (Blueprint $table) {
        $table->id();
        $table->integer('user_id');      // Người nhận thông báo (chủ bài viết)
        $table->integer('sender_id');    // Người gây ra hành động (người like/comment)
        $table->integer('post_id')->nullable(); // Bài viết liên quan
        $table->string('type');          // Loại: 'like', 'comment'
        $table->text('message');         // Nội dung hiển thị
        $table->tinyInteger('is_read')->default(0); // 0: chưa đọc, 1: đã đọc
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
    
};
