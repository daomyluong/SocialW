<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FeedSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $users = [
            [
                'name' => 'Dao Nguyen',
                'email' => 'dao@example.com',
                'username' => 'dao_ws',
                'display_name' => 'Dao W-Social',
                'bio' => 'Leader team W-Social',
                'avatar_url' => null,
            ],
            [
                'name' => 'Loan Tran',
                'email' => 'loan@example.com',
                'username' => 'loan_auth',
                'display_name' => 'Loan Auth',
                'bio' => 'Auth and profile module',
                'avatar_url' => null,
            ],
            [
                'name' => 'Thanh Le',
                'email' => 'thanh@example.com',
                'username' => 'thanh_posts',
                'display_name' => 'Thanh Posts',
                'bio' => 'Posts module owner',
                'avatar_url' => null,
            ],
            [
                'name' => 'Quynh Pham',
                'email' => 'quynh@example.com',
                'username' => 'quynh_social',
                'display_name' => 'Quynh Social',
                'bio' => 'Social interaction module',
                'avatar_url' => null,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make('password'),
                    'username' => $user['username'],
                    'display_name' => $user['display_name'],
                    'bio' => $user['bio'],
                    'avatar_url' => $user['avatar_url'],
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        $daoId = (int) DB::table('users')->where('email', 'dao@example.com')->value('id');
        $loanId = (int) DB::table('users')->where('email', 'loan@example.com')->value('id');
        $thanhId = (int) DB::table('users')->where('email', 'thanh@example.com')->value('id');
        $quynhId = (int) DB::table('users')->where('email', 'quynh@example.com')->value('id');

        $posts = [
            [
                'user_id' => $loanId,
                'content' => 'TV2 da xong khung Auth/Profile. Moi nguoi review luong profile nhe.',
                'visibility' => 'public',
                'like_count' => 3,
                'comment_count' => 1,
                'created_at' => $now->copy()->subMinutes(25),
            ],
            [
                'user_id' => $thanhId,
                'content' => 'TV3 vua bo sung route posts index/create/store. Dang tiep tuc validate noi dung.',
                'visibility' => 'public',
                'like_count' => 5,
                'comment_count' => 2,
                'created_at' => $now->copy()->subMinutes(16),
            ],
            [
                'user_id' => $quynhId,
                'content' => 'TV4 da xong API follow co ban. Chuan bi cap nhat like/unlike ngay toi.',
                'visibility' => 'follower',
                'like_count' => 4,
                'comment_count' => 0,
                'created_at' => $now->copy()->subMinutes(8),
            ],
        ];

        foreach ($posts as $post) {
            DB::table('posts')->updateOrInsert(
                [
                    'user_id' => $post['user_id'],
                    'content' => $post['content'],
                ],
                [
                    'media_id' => null,
                    'visibility' => $post['visibility'],
                    'like_count' => $post['like_count'],
                    'comment_count' => $post['comment_count'],
                    'is_deleted' => false,
                    'updated_at' => $now,
                    'created_at' => $post['created_at'],
                ]
            );
        }

        if ($daoId && $quynhId) {
            DB::table('followers')->updateOrInsert(
                [
                    'follower_user_id' => $daoId,
                    'following_user_id' => $quynhId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if ($daoId && $loanId) {
            DB::table('followers')->updateOrInsert(
                [
                    'follower_user_id' => $daoId,
                    'following_user_id' => $loanId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        if ($loanId && $thanhId) {
            DB::table('followers')->updateOrInsert(
                [
                    'follower_user_id' => $loanId,
                    'following_user_id' => $thanhId,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
