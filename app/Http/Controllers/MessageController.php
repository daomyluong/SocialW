<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index(): View
    {
        $viewer = $this->resolveViewer();
        $followingUsers = $this->followingUsers($viewer);
        $groupConversations = $this->groupConversations($viewer);
        $activeConversation = null;
        $recentPrivateConversations = $this->recentPrivateConversations($viewer);

        return $this->renderPage($viewer, $followingUsers, $groupConversations, $activeConversation, $recentPrivateConversations);
    }

    public function openPrivate(User $user): RedirectResponse
    {
        $viewer = $this->resolveViewer();

        abort_if($viewer->is($user), 422, 'Không thể nhắn tin với chính bạn.');

        $conversation = $this->findOrCreatePrivateConversation($viewer, $user);

        return redirect()->route('messages.show', $conversation);
    }

    public function show(Conversation $conversation): View
    {
        $viewer = $this->resolveViewer();
        $this->ensureParticipant($conversation, $viewer);

        $followingUsers = $this->followingUsers($viewer);
        $groupConversations = $this->groupConversations($viewer);
        $recentPrivateConversations = $this->recentPrivateConversations($viewer);

        return $this->renderPage($viewer, $followingUsers, $groupConversations, $conversation->load(['participants', 'creator']), $recentPrivateConversations);
    }

    public function history(Conversation $conversation): JsonResponse
    {
        $viewer = $this->resolveViewer();
        $this->ensureParticipant($conversation, $viewer);

        $messages = $conversation->messages()
            ->with(['sender:id,username,display_name,name,avatar_url'])
            ->oldest('created_at')
            ->get()
            ->map(function (Message $message): array {
                return [
                    'id' => $message->id,
                    'body' => $message->body,
                    'attachment_type' => $message->attachment_type,
                    'attachment_name' => $message->attachment_name,
                    'attachment_mime' => $message->attachment_mime,
                    'attachment_size' => $message->attachment_size,
                    'attachment_url' => $message->attachment_path
                        ? asset('storage/'.$message->attachment_path)
                        : null,
                    'created_at' => optional($message->created_at)->toIso8601String(),
                    'created_at_human' => optional($message->created_at)->diffForHumans(),
                    'sender' => [
                        'id' => $message->sender?->id,
                        'username' => $message->sender?->username ?? 'guest',
                        'display_name' => $message->sender?->display_name ?? $message->sender?->name ?? 'Người dùng',
                        'avatar_url' => $message->sender?->avatar_url,
                    ],
                ];
            });

        return response()->json([
            'data' => $messages,
            'meta' => [
                'count' => $messages->count(),
                'conversation_id' => $conversation->id,
                'server_time' => now()->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        $viewer = $this->resolveViewer();
        $this->ensureParticipant($conversation, $viewer);

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'file', 'image', 'max:10240'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip,rar', 'max:20480'],
        ]);

        $hasBody = trim((string) ($data['body'] ?? '')) !== '';
        $imageFile = $request->file('image');
        $attachmentFile = $request->file('attachment');

        if (! $hasBody && ! $imageFile && ! $attachmentFile) {
            return redirect()
                ->route('messages.show', $conversation)
                ->withErrors(['body' => 'Vui lòng nhập tin nhắn hoặc chọn ảnh/tài liệu để gửi.']);
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;
        $attachmentType = null;

        if ($imageFile) {
            $attachmentPath = $imageFile->store('chat/images', 'public');
            $attachmentName = $imageFile->getClientOriginalName();
            $attachmentMime = $imageFile->getClientMimeType();
            $attachmentSize = $imageFile->getSize();
            $attachmentType = 'image';
        } elseif ($attachmentFile) {
            $attachmentPath = $attachmentFile->store('chat/files', 'public');
            $attachmentName = $attachmentFile->getClientOriginalName();
            $attachmentMime = $attachmentFile->getClientMimeType();
            $attachmentSize = $attachmentFile->getSize();
            $attachmentType = 'file';
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_user_id' => $viewer->id,
            'body' => $hasBody ? trim((string) $data['body']) : null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
            'attachment_type' => $attachmentType,
            'is_read' => false,
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();

        return redirect()->route('messages.show', $conversation)->with('message_sent', true);
    }

    private function renderPage(User $viewer, Collection $followingUsers, Collection $groupConversations, ?Conversation $activeConversation, Collection $recentPrivateConversations): View
    {
        $messages = $activeConversation
            ? $activeConversation->messages()
                ->with(['sender:id,username,display_name,name,avatar_url'])
                ->oldest('created_at')
                ->get()
            : collect();

        $activeConversation = $activeConversation?->load(['participants:id,username,display_name,name,avatar_url', 'creator:id,username,display_name,name']);
        $activeConversationAvatar = null;

        if ($activeConversation && $activeConversation->type === 'private') {
            $otherParticipant = $activeConversation->participants->first(fn (User $participant): bool => $participant->id !== $viewer->id);
            $activeConversationAvatar = $otherParticipant?->avatar_url;
        }

        return view('messages.index', [
            'viewer' => $viewer,
            'followingUsers' => $followingUsers,
            'groupConversations' => $groupConversations,
            'recentPrivateConversations' => $recentPrivateConversations,
            'activeConversation' => $activeConversation,
            'activeConversationAvatar' => $activeConversationAvatar,
            'messages' => $messages,
            'activeConversationTitle' => $this->conversationTitle($viewer, $activeConversation),
            'activeConversationSubtitle' => $this->conversationSubtitle($viewer, $activeConversation),
        ]);
    }

    private function resolveViewer(): User
    {
        return Auth::user() ?? User::query()->orderBy('id')->firstOrFail();
    }

    private function followingUsers(User $viewer): Collection
    {
        $followingIds = DB::table('followers')
            ->where('follower_user_id', $viewer->id)
            ->pluck('following_user_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($followingIds === []) {
            return collect();
        }

        return User::query()
            ->select(['id', 'username', 'display_name', 'name', 'avatar_url'])
            ->whereIn('id', $followingIds)
            ->orderByRaw('COALESCE(display_name, name) asc')
            ->get();
    }

    private function groupConversations(User $viewer): Collection
    {
        return Conversation::query()
            ->with(['participants:id,username,display_name,name,avatar_url', 'creator:id,username,display_name,name'])
            ->withCount('messages')
            ->where('type', 'group')
            ->whereHas('participants', function ($query) use ($viewer): void {
                $query->where('user_id', $viewer->id);
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    private function recentPrivateConversations(User $viewer): Collection
    {
        $conversations = Conversation::query()
            ->with([
                'participants:id,username,display_name,name,avatar_url',
                'messages' => function ($query): void {
                    $query->with('sender:id,username,display_name,name')
                        ->latest('created_at')
                        ->limit(1);
                },
            ])
            ->where('type', 'private')
            ->whereHas('participants', function ($query) use ($viewer): void {
                $query->where('user_id', $viewer->id);
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return $conversations->map(function (Conversation $conversation) use ($viewer): array {
            $otherParticipant = $conversation->participants->first(fn (User $participant): bool => $participant->id !== $viewer->id);
            $lastMessage = $conversation->messages->first();

            return [
                'conversation' => $conversation,
                'other_user' => $otherParticipant,
                'last_message' => $lastMessage,
                'last_time_human' => optional($conversation->last_message_at ?? $lastMessage?->created_at)->diffForHumans(),
            ];
        });
    }

    private function defaultConversation(User $viewer, Collection $followingUsers, Collection $groupConversations): ?Conversation
    {
        if ($followingUsers->isNotEmpty()) {
            return $this->findOrCreatePrivateConversation($viewer, $followingUsers->first());
        }

        if ($groupConversations->isNotEmpty()) {
            return $groupConversations->first();
        }

        return null;
    }

    private function findOrCreatePrivateConversation(User $viewer, User $otherUser): Conversation
    {
        $viewerId = (int) $viewer->id;
        $otherUserId = (int) $otherUser->id;
        $pair = [min($viewerId, $otherUserId), max($viewerId, $otherUserId)];
        $conversationKey = sprintf('private:%d:%d', $pair[0], $pair[1]);

        $conversation = Conversation::query()
            ->with(['participants:id,username,display_name,name,avatar_url', 'creator:id,username,display_name,name'])
            ->where('conversation_key', $conversationKey)
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $conversation = Conversation::create([
            'conversation_key' => $conversationKey,
            'type' => 'private',
            'title' => null,
            'created_by_user_id' => $viewerId,
            'last_message_at' => null,
        ]);

        $conversation->participants()->attach([$viewerId, $otherUserId]);

        return $conversation->load(['participants:id,username,display_name,name,avatar_url', 'creator:id,username,display_name,name']);
    }

    private function ensureParticipant(Conversation $conversation, User $viewer): void
    {
        $isParticipant = $conversation->participants()
            ->where('users.id', $viewer->id)
            ->exists();

        abort_unless($isParticipant, 403);
    }

    private function conversationTitle(User $viewer, ?Conversation $conversation): string
    {
        if (! $conversation) {
            return 'Chọn cuộc trò chuyện';
        }

        if ($conversation->type === 'group') {
            return $conversation->title ?? 'Nhóm chat';
        }

        $otherParticipant = $conversation->participants->first(fn (User $participant): bool => $participant->id !== $viewer->id);

        return $otherParticipant?->display_name ?? $otherParticipant?->name ?? $otherParticipant?->username ?? 'Tin nhắn riêng';
    }

    private function conversationSubtitle(User $viewer, ?Conversation $conversation): string
    {
        if (! $conversation) {
            return 'Danh sách bên trái là những người bạn đang theo dõi và các nhóm đang theo dõi.';
        }

        if ($conversation->type === 'group') {
            return sprintf('%d thành viên · %d tin nhắn', $conversation->participants->count(), $conversation->messages()->count());
        }

        $otherParticipant = $conversation->participants->first(fn (User $participant): bool => $participant->id !== $viewer->id);

        return '@'.($otherParticipant?->username ?? 'guest');
    }
}