<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Story3 extends Model {
    protected $table = 'stories'; 
    protected $fillable = ['user_id', 'media_url', 'type'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive24h($query) {
        return $query->where('created_at', '>=', now()->subHours(24));
    }
}