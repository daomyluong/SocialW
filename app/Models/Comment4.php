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
        'author_user_id',
        'parent_comment_id',
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
        return $this->belongsTo(User::class, 'author_user_id');
    }

    // Một bình luận có thể là câu trả lời cho một bình luận khác.
    public function parent()
    {
        return $this->belongsTo(Comment4::class, 'parent_comment_id');
    }

    //Một bình luận có thể có nhiều câu trả lời (con).
    public function replies()
    {
        return $this->hasMany(Comment4::class, 'parent_comment_id');
    }
}