<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark3 extends Model
{
    protected $table = 'bookmarks3';

    protected $fillable = ['user_id', 'post_id', 'folder_name', 'is_deleted'];
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
    // Chỉ lấy những bookmark chưa bị xóa mềm
    public function scopeActive($query)
    {
    return $query->where('is_deleted', 0);
    }
}