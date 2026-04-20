<?php

namespace App\Models;

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
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Chỉ định bảng tương ứng trong social.sql
    protected $table = 'users'; 

    // Cập nhật các cột được phép thêm dữ liệu (theo file social.sql của bạn)
    protected $fillable = [
        'username',
        'display_name',
        'email',
        'password_hash', // Lưu ý: trong SQL bạn đặt là password_hash chứ không phải password
        'bio',
        'avatar_media_id',
        'cover_media_id',
        'post_count',
        'follower_count',
        'following_count',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    // Ghi đè phương thức này vì Laravel mặc định tìm cột 'password' để đăng nhập
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }
}
