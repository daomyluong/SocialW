<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post4 extends Model
{
    use HasFactory;

    // Chỉ định tên bảng nếu cần thiết (Laravel tự động nhận dạng bảng 'posts', nhưng khai báo cho chắc chắn)
    protected $table = 'posts';

    // Các trường có thể được gán dữ liệu hàng loạt (Mass assignment)
    protected $fillable = [
        'author_user_id',
        'content',
        'like_count',
        'comment_count',
        'share_count',
        'visibility',
        'is_deleted',
    ];

    // ==========================================
    // MỐI QUAN HỆ (RELATIONSHIPS)
    // ==========================================

    /**
     * Một bài viết thuộc về một người dùng (tác giả).
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    /**
     * Một bài viết có thể có nhiều bình luận.
     */
    public function comments()
    {
        return $this->hasMany(Comment4::class, 'post_id');
    }

    /**
     * Một bài viết có thể có nhiều người thích.
     * (Quan hệ Many-to-Many thông qua bảng trung gian 'post_likes')
     */
    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')->withTimestamps();
    }

    // ==========================================
    // CÁC HÀM TIỆN ÍCH (HELPER METHODS)
    // ==========================================

    /**
     * Kiểm tra xem một user cụ thể đã like bài viết này chưa.
     */
    public function isLikedBy(User $user)
    {
        // Kiểm tra xem ID của user có nằm trong danh sách những người đã like bài viết này không
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}