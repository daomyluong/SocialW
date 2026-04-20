<?php

namespace Tests\Feature\TV1;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_loads_messages_page(): void
    {
        $viewer = User::query()->where('email', 'dao@example.com')->firstOrFail();

        $this->actingAs($viewer)
            ->get('/messages')
            ->assertOk()
            ->assertSee('Nhắn tin riêng');
    }

    public function test_it_can_send_message_in_private_conversation(): void
    {
        $viewer = User::query()->where('email', 'dao@example.com')->firstOrFail();
        $contact = User::query()->where('email', 'loan@example.com')->firstOrFail();

        $conversation = Conversation::query()
            ->where('conversation_key', sprintf('private:%d:%d', min($viewer->id, $contact->id), max($viewer->id, $contact->id)))
            ->firstOrFail();

        $this->actingAs($viewer)
            ->post(route('messages.store', $conversation), [
                'body' => 'Tin nhan test tu feature test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_user_id' => $viewer->id,
            'body' => 'Tin nhan test tu feature test',
        ]);
    }
}