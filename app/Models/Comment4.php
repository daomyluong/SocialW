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

    protected static function booted()
    {
        static::addGlobalScope('visible_and_active', function ($builder) {
            $builder->where('comments.is_deleted', 0)
                    ->where(function ($query) {
                        $query->whereNull('comments.status')
                              ->orWhere('comments.status', '!=', 'hidden');
                    });
        });
    }
    
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user()
    {
        return $this->author();
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    // Thêm quan hệ likes
    public function likes()
    {
        return $this->belongsToMany(User::class, 'comment_likes', 'comment_id', 'user_id')->withTimestamps();
    }

    // Kiểm tra user hiện tại đã like bình luận này chưa
    public function isLikedBy($user)
    {
        if (!$user) return false;
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}