<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'user_id', 'content', 'media_id', 'like_count', 
        'comment_count', 'share_count', 'visibility', 
        'is_deleted', 'is_edited',
    ];

    protected function casts(): array {
        return ['is_deleted' => 'boolean', 'is_edited' => 'boolean'];
    }

    // Quan hệ với User
    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo {
        return $this->author();
    }

    // Quan hệ với Media
    public function media(): BelongsToMany {
        return $this->belongsToMany(Media::class, 'post_media', 'post_id', 'media_id');
    }

    // Quan hệ với Comment
    public function comments(): HasMany {
        return $this->hasMany(Comment4::class, 'post_id');
    }

    // Quan hệ với Likes
    public function likes(): BelongsToMany {
        return $this->belongsToMany(User::class, 'post_likes', 'post_id', 'user_id')->withTimestamps();
    }

    public function isLikedBy(User $user): bool {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    /**
     * "Ống lọc" bài viết: Chỉ lấy bài chưa xóa và đúng quyền xem
     */
    public function scopeVisible($query)
    {
        $currentUserId = auth()->id();

        return $query->where('is_deleted', 0)
            ->where(function ($q) use ($currentUserId) {
                // 1. Nếu là chủ bài viết: Thấy hết (kể cả bài Private)
                $q->where('user_id', $currentUserId)
                // 2. Nếu là bài Public: Ai cũng thấy
                ->orWhere('visibility', 'public');
                
                if ($currentUserId) {
                    // 3. Nếu là bài Follower: Chỉ những người đang theo dõi tác giả mới thấy
                    $q->orWhere(function ($sub) use ($currentUserId) {
                        $sub->where('visibility', 'follower')
                            ->whereIn('user_id', function ($f) use ($currentUserId) {
                                $f->select('following_user_id')
                                  ->from('followers')
                                  ->where('follower_user_id', $currentUserId);
                            });
                    });
                }
            });
    }
}