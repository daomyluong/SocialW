<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessagingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $daoId = (int) DB::table('users')->where('email', 'dao@example.com')->value('id');
        $loanId = (int) DB::table('users')->where('email', 'loan@example.com')->value('id');
        $thanhId = (int) DB::table('users')->where('email', 'thanh@example.com')->value('id');
        $quynhId = (int) DB::table('users')->where('email', 'quynh@example.com')->value('id');
        $testerId = (int) DB::table('users')->orderBy('id')->value('id');

        if (! $daoId || ! $loanId || ! $thanhId || ! $quynhId || ! $testerId) {
            return;
        }

        // Follow mẫu để tài khoản test có danh sách người đang theo dõi trong module nhắn tin.
        $this->ensureFollow($testerId, $daoId, $now);
        $this->ensureFollow($testerId, $loanId, $now);
        $this->ensureFollow($testerId, $thanhId, $now);

        $this->ensureFollow($daoId, $loanId, $now);
        $this->ensureFollow($daoId, $quynhId, $now);
        $this->ensureFollow($loanId, $thanhId, $now);

        if ($testerId !== $daoId) {
            $this->ensureFollow($daoId, $testerId, $now);
        }

        $daoLoanConversation = $this->ensurePrivateConversation($daoId, $loanId, $daoId, $now->copy()->subMinutes(12));
        $testerDaoConversation = $this->ensurePrivateConversation($testerId, $daoId, $testerId, $now->copy()->subMinutes(9));
        $testerLoanConversation = $this->ensurePrivateConversation($testerId, $loanId, $testerId, $now->copy()->subMinutes(6));

        $groupConversations = [
            [
                'key' => 'group:tv1-coordination',
                'title' => 'Nhóm TV1 điều phối',
                'members' => [$daoId, $loanId, $thanhId, $testerId],
                'creator' => $daoId,
                'last_message_at' => $now->copy()->subMinutes(18),
                'messages' => [
                    ['sender_user_id' => $daoId, 'body' => 'Mọi người cập nhật tiến độ TV1 giúp mình nhé.'],
                    ['sender_user_id' => $loanId, 'body' => 'Mình đang giữ phần sidebar và profile.'],
                ],
            ],
            [
                'key' => 'group:tv4-social',
                'title' => 'Nhóm TV4 social',
                'members' => [$daoId, $quynhId, $thanhId, $testerId],
                'creator' => $quynhId,
                'last_message_at' => $now->copy()->subMinutes(7),
                'messages' => [
                    ['sender_user_id' => $quynhId, 'body' => 'Mình đã nối follow flow cơ bản cho TV4.'],
                    ['sender_user_id' => $thanhId, 'body' => 'Mình sẽ xem lại phần visibility của bài viết.'],
                    ['sender_user_id' => $testerId, 'body' => 'Mình đã vào test màn hình nhắn tin, phần gửi tin ổn rồi.'],
                ],
            ],
        ];

        foreach ($groupConversations as $groupData) {
            $conversation = Conversation::query()->where('conversation_key', $groupData['key'])->first();

            if (! $conversation) {
                $conversation = Conversation::create([
                    'conversation_key' => $groupData['key'],
                    'type' => 'group',
                    'title' => $groupData['title'],
                    'created_by_user_id' => $groupData['creator'],
                    'last_message_at' => $groupData['last_message_at'],
                ]);
            }

            $conversation->participants()->syncWithoutDetaching(array_values(array_unique($groupData['members'])));

            foreach ($groupData['messages'] as $index => $messageData) {
                Message::query()->updateOrCreate(
                    [
                        'conversation_id' => $conversation->id,
                        'sender_user_id' => $messageData['sender_user_id'],
                        'body' => $messageData['body'],
                    ],
                    [
                        'is_read' => false,
                        'created_at' => $groupData['last_message_at']->copy()->addMinutes($index),
                        'updated_at' => $groupData['last_message_at']->copy()->addMinutes($index),
                    ]
                );
            }
        }

        $daoLoanMessages = [
            ['sender_user_id' => $daoId, 'body' => 'Loan ơi, bạn xem giúp mình UI nhắn tin nhé.'],
            ['sender_user_id' => $loanId, 'body' => 'Ok, mình sẽ làm phần sidebar và khung chat trước.'],
        ];

        foreach ($daoLoanMessages as $index => $messageData) {
            Message::query()->updateOrCreate(
                [
                    'conversation_id' => $daoLoanConversation->id,
                    'sender_user_id' => $messageData['sender_user_id'],
                    'body' => $messageData['body'],
                ],
                [
                    'is_read' => false,
                    'created_at' => $now->copy()->subMinutes(12 - $index),
                    'updated_at' => $now->copy()->subMinutes(12 - $index),
                ]
            );
        }

        $daoLoanConversation->forceFill([
            'last_message_at' => $now->copy()->subMinutes(11),
        ])->save();

        $testerDaoMessages = [
            ['sender_user_id' => $testerId, 'body' => 'Chào bạn, mình đang test chức năng nhắn tin riêng.'],
            ['sender_user_id' => $daoId, 'body' => 'OK, bạn thử nhập nội dung rồi bấm Gửi nhé.'],
            ['sender_user_id' => $testerId, 'body' => 'Mình đã thấy ô nhập và gửi hoạt động bình thường.'],
        ];

        foreach ($testerDaoMessages as $index => $messageData) {
            Message::query()->updateOrCreate(
                [
                    'conversation_id' => $testerDaoConversation->id,
                    'sender_user_id' => $messageData['sender_user_id'],
                    'body' => $messageData['body'],
                ],
                [
                    'is_read' => false,
                    'created_at' => $now->copy()->subMinutes(9 - $index),
                    'updated_at' => $now->copy()->subMinutes(9 - $index),
                ]
            );
        }

        $testerDaoConversation->forceFill([
            'last_message_at' => $now->copy()->subMinutes(7),
        ])->save();

        $testerLoanMessages = [
            ['sender_user_id' => $loanId, 'body' => 'Mình vừa bổ sung icon emoji ở ô nhập rồi nhé.'],
            ['sender_user_id' => $testerId, 'body' => 'Đã test, thao tác gửi tin nhắn ok.'],
        ];

        foreach ($testerLoanMessages as $index => $messageData) {
            Message::query()->updateOrCreate(
                [
                    'conversation_id' => $testerLoanConversation->id,
                    'sender_user_id' => $messageData['sender_user_id'],
                    'body' => $messageData['body'],
                ],
                [
                    'is_read' => false,
                    'created_at' => $now->copy()->subMinutes(6 - $index),
                    'updated_at' => $now->copy()->subMinutes(6 - $index),
                ]
            );
        }

        $testerLoanConversation->forceFill([
            'last_message_at' => $now->copy()->subMinutes(5),
        ])->save();
    }

    private function privateKey(int $firstUserId, int $secondUserId): string
    {
        $pair = [min($firstUserId, $secondUserId), max($firstUserId, $secondUserId)];

        return sprintf('private:%d:%d', $pair[0], $pair[1]);
    }

    private function ensureFollow(int $followerUserId, int $followingUserId, $now): void
    {
        if ($followerUserId <= 0 || $followingUserId <= 0 || $followerUserId === $followingUserId) {
            return;
        }

        DB::table('followers')->updateOrInsert(
            [
                'follower_user_id' => $followerUserId,
                'following_user_id' => $followingUserId,
            ],
            [
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function ensurePrivateConversation(int $firstUserId, int $secondUserId, int $creatorId, $lastMessageAt): Conversation
    {
        $conversation = Conversation::query()->where('conversation_key', $this->privateKey($firstUserId, $secondUserId))->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'conversation_key' => $this->privateKey($firstUserId, $secondUserId),
                'type' => 'private',
                'title' => null,
                'created_by_user_id' => $creatorId,
                'last_message_at' => $lastMessageAt,
            ]);
        }

        $conversation->participants()->syncWithoutDetaching([$firstUserId, $secondUserId]);

        return $conversation;
    }
}