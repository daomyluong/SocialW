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
        Schema::create('comment_likes', function ($table) {
        $table->id();
        // Giả sử bảng users là 'users' và bảng comments là 'comments'
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
        $table->foreignId('comment_id')->constrained('comments')->onDelete('cascade');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
    }
};
