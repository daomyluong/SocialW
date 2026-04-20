<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $timestamps = true; 

    protected $fillable = [
        'user_id', 
        'actor_user_id',
        'type',
        'post_id', 
        'comment_id',   
        'is_read'
    ];

    public function actor()
    {
        
        return $this->belongsTo(User::class, 'actor_user_id');
    }
    
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}