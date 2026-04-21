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
        'author_user_id',
        'content',
        'media_id',
        'like_count',
        'comment_count',
        'share_count',
        'visibility',
        'is_deleted',
        'is_edited',
    ];

    protected function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
            'is_edited' => 'boolean',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_user_id');
    }

    public function user(): BelongsTo
    {
        return $this->author();
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'post_media', 'post_id', 'media_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment4::class, 'post_id');
    }
}
