<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Post extends Model
{
    use HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'user_id',
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
        $authorColumn = Schema::hasTable('posts') && Schema::hasColumn('posts', 'user_id')
            ? 'user_id'
            : 'author_user_id';

        return $this->belongsTo(User::class, $authorColumn);
    }

    public function user(): BelongsTo
    {
        return $this->author();
    }

    public function getAuthorIdAttribute()
    {

        return $this->author_user_id;
    }

    public function setAuthorUserIdAttribute(mixed $value): void
    {
        if (Schema::hasTable('posts') && Schema::hasColumn('posts', 'user_id')) {
            $this->attributes['user_id'] = $value;

            return;
        }

        $this->attributes['author_user_id'] = $value;
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
