<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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