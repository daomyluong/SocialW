<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment4 extends Model
{
    use HasFactory;

    protected $table = 'comments';

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'is_deleted',
    ];

    // Một bình luận thuộc về một bài viết.
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    // Một bình luận được viết bởi một người dùng.
    public function user() // Đổi tên hàm thành user cho tự nhiên, hoặc author cũng được
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}