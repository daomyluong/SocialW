<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['display_name', 'username', 'email', 'password', 'avatar_url'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use Notifiable;
  protected $fillable = [
    'display_name',
    'username',
    'email',
    'password_hash',
    'avatar_url',
    'bio',
];
    public function getAuthPassword()
    {
    return $this->password_hash; // Nói với Laravel: "Hãy lấy mật khẩu ở cột password_hash"
    }
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_user_id', 'following_user_id')->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'following_user_id', 'follower_user_id')->withTimestamps();
    }
    
}
