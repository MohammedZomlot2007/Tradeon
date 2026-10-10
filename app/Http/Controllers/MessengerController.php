<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\AuditLog;
use App\Models\ChatRequest;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserBlock;
use App\Notifications\ChatMessageReceived;
use App\Notifications\ChatRequestReceived;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessengerController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = auth()->id();

        $conversations = Conversation::where('user_one_id', $userId)
            ->orWhere('user_two_id', $userId)
            ->with(['userOne', 'userTwo', 'companyOne', 'companyTwo', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->get();

        $chatRequests = ChatRequest::where('receiver_id', $userId)
            ->where('status', 'pending')
            ->with('sender')
            ->latest()
            ->get();

        $activeConversationId = $request->query('conversation_id');
        $recipientId = $request->integer('recipient');
        $purpose = trim((string) $request->query('purpose', 'general'));
        $contactTarget = null;
        $activeConversation = null;

        if ($activeConversationId) {
            $activeConversation = $this->authorizedConversation((int) $activeConversationId);
            $activeConversation->setRelation(
                'messages',
                $activeConversation->messages()->with('sender')->latest()->limit(100)->get()->reverse()->values()
            );
        } elseif ($recipientId && $recipientId !== $userId) {
            $contactTarget = User::query()->with('company')->find($recipientId);
            abort_unless($contactTarget, 404);
        } elseif ($conversations->isNotEmpty()) {
            $activeConversation = $conversations->first()->load(['userOne', 'userTwo', 'companyOne', 'companyTwo']);
            $activeConversation->setRelation(
                'messages',
                $activeConversation->messages()->with('sender')->latest()->limit(100)->get()->reverse()->values()
            );
        }

        return Inertia::render('Messenger/Index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'chatRequests' => $chatRequests,
            'contactTarget' => $contactTarget,
            'contactPurpose' => $purpose,
            'currentUserId' => $userId,
        ]);
    }

    public function sendMessage(Request $request, int $conversationId)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:10000',
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['string', 'max:2048'],
        ]);

        $conversation = $this->authorizedConversation($conversationId);

        $otherUserId = $conversation->user_one_id === auth()->id()
            ? $conversation->user_two_id
            : $conversation->user_one_id;
        $this->abortIfBlocked($otherUserId);

        $message = $conversation->messages()->create([
            'sender_id' => auth()->id(),
            'body' => trim($validated['body']),
            'attachments' => $validated['attachments'] ?? [],
        ]);

        $conversation->update(['last_message_at' => now()]);

        $recipient = $message->sender_id === $conversation->user_one_id ? $conversation->userTwo : $conversation->userOne;
        $recipient->notify(new ChatMessageReceived($message->load('sender')));
        MessageSent::dispatch($message);
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'message.sent', 'auditable_type' => Message::class, 'auditable_id' => $message->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);

        return redirect()->back();
    }

    public function sendChatRequest(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id|different:'.auth()->id(),
            'purpose' => 'required|string|max:255',
            'initial_message' => 'required|string|max:5000',
        ]);

        abort_unless((int) $validated['receiver_id'] !== auth()->id(), 422, 'You cannot send a chat request to yourself.');
        $this->abortIfBlocked((int) $validated['receiver_id']);

        $receiver = User::findOrFail($validated['receiver_id']);
        abort_if($receiver->isSuspended(), 422, 'This account is not available.');

        $existing = ChatRequest::whereIn('status', ['pending', 'accepted'])
            ->where(function ($query) use ($validated) {
                $query->where(function ($q) use ($validated) {
                    $q->where('sender_id', auth()->id())->where('receiver_id', $validated['receiver_id']);
                })->orWhere(function ($q) use ($validated) {
                    $q->where('sender_id', $validated['receiver_id'])->where('receiver_id', auth()->id());
                });
            })
            ->exists();

        abort_if($existing, 409, 'An active chat relationship already exists.');

        $chatRequest = ChatRequest::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $validated['receiver_id'],
            'purpose' => $validated['purpose'],
            'initial_message' => $validated['initial_message'],
            'status' => 'pending',
        ]);

        User::findOrFail($validated['receiver_id'])->notify(new ChatRequestReceived($chatRequest->load('sender')));
        AuditLog::create(['user_id' => auth()->id(), 'action' => 'chat_request.created', 'auditable_type' => ChatRequest::class, 'auditable_id' => $chatRequest->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);

        return redirect()->back()->with('success', __('flash.contact_sent'));
    }

    public function respondChatRequest(Request $request, int $id)
    {
        $chatReq = ChatRequest::findOrFail($id);
        abort_unless($chatReq->receiver_id === auth()->id(), 403);

        $validated = $request->validate([
            'action' => 'required|in:accept,decline',
        ]);

        if ($validated['action'] === 'accept') {
            $chatReq->update(['status' => 'accepted']);

            $conversation = Conversation::firstOrCreate(
                [
                    'user_one_id' => min($chatReq->sender_id, $chatReq->receiver_id),
                    'user_two_id' => max($chatReq->sender_id, $chatReq->receiver_id),
                ],
                [
                    'company_one_id' => Company::where('user_id', min($chatReq->sender_id, $chatReq->receiver_id))->value('id'),
                    'company_two_id' => Company::where('user_id', max($chatReq->sender_id, $chatReq->receiver_id))->value('id'),
                    'last_message_at' => now(),
                ]
            );

            if (! $conversation->messages()->where('sender_id', $chatReq->sender_id)->where('body', $chatReq->initial_message)->exists()) {
                $conversation->messages()->create([
                    'sender_id' => $chatReq->sender_id,
                    'body' => $chatReq->initial_message,
                ]);
            }
            $conversation->update(['last_message_at' => now()]);
        } else {
            $chatReq->update(['status' => 'declined']);
        }

        return redirect()->back();
    }

    private function authorizedConversation(int $conversationId): Conversation
    {
        return Conversation::query()
            ->whereKey($conversationId)
            ->where(function ($query) {
                $query->where('user_one_id', auth()->id())
                    ->orWhere('user_two_id', auth()->id());
            })
            ->with(['userOne', 'userTwo', 'companyOne', 'companyTwo'])
            ->firstOrFail();
    }

    private function abortIfBlocked(int $otherUserId): void
    {
        $blocked = UserBlock::where(function ($query) use ($otherUserId) {
            $query->where('user_id', auth()->id())->where('blocked_user_id', $otherUserId);
        })->orWhere(function ($query) use ($otherUserId) {
            $query->where('user_id', $otherUserId)->where('blocked_user_id', auth()->id());
        })->exists();

        abort_if($blocked, 403, 'This conversation is not available.');
    }
}
