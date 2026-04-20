<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('bookmarks3', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id'); // ID người lưu
        $table->unsignedBigInteger('post_id'); // ID bài viết được lưu
        $table->string('folder_name')->default('Tất cả'); // Tên thư mục phân loại
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('bookmarks3');
}
};
