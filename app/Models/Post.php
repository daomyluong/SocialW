<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    // 2. Danh sách các cột được phép thêm dữ liệu
    protected $fillable = [
        'author_user_id', 
        'content', 
        'visibility', 
        'is_deleted' ,
        'is_edited'
    ];

    // 3.Một bài viết thuộc về một người dùng 
    public function user() {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    // 4. Một bài viết có nhiều ảnh qua bảng trung gian
    public function media() {
        return $this->belongsToMany(Media::class, 'post_media', 'post_id', 'media_id');
    }

    // 5. Một bài viết có nhiều bình luận
    public function comments() {
        return $this->hasMany(Comment4::class, 'post_id');
    }
    
}
