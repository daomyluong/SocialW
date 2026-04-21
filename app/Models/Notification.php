<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = true; 

    protected $fillable = [
        'user_id', 
        'sender_id',
        'type',
        'post_id', 
        'comment_id',
        'message',
        'is_read'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function actor()
    {
        return $this->sender();
    }
    
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}