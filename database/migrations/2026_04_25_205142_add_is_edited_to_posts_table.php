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
        Schema::table('posts', function (Blueprint $table) {
            // Thêm cột is_edited kiểu boolean (mặc định là 0 - chưa sửa)
            $table->boolean('is_edited')->default(0)->after('is_deleted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Xóa cột nếu chạy lệnh rollback
            $table->dropColumn('is_edited');
        });
    }
};
