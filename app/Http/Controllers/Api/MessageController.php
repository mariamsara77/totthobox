<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use App\Models\Block;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Events\MessageDeleted;
use App\Jobs\SendUserPushJob;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    public function users(Request $request)
    {
        $authId = $request->user()->id;
        $search = $request->query('q');

        $users = User::where('id', '!=', $authId)
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }, function ($q) use ($authId) {
                $q->where(function ($sub) use ($authId) {
                    $sub->whereHas('sentMessages', fn ($m) => $m->where('receiver_id', $authId))
                        ->orWhereHas('receivedMessages', fn ($m) => $m->where('sender_id', $authId));
                });
            })
            ->with([
                'sentMessages' => fn ($q) => $q->latest()->limit(1),
                'receivedMessages' => fn ($q) => $q->latest()->limit(1),
                'media',
            ])
            ->get()
            ->sortByDesc(function ($user) {
                $lastSent = optional($user->sentMessages->first())->created_at?->timestamp ?? 0;
                $lastReceived = optional($user->receivedMessages->first())->created_at?->timestamp ?? 0;
                return max($lastSent, $lastReceived);
            })
            ->values();

        return response()->json($users);
    }

    public function index(Request $request, User $user)
    {
        $perPage = $request->integer('per_page', 300);

        $messages = Message::where(function ($q) use ($user, $request) {
            $q->where(fn ($sub) => $sub->where('sender_id', $request->user()->id)->where('receiver_id', $user->id))
                ->orWhere(fn ($sub) => $sub->where('sender_id', $user->id)->where('receiver_id', $request->user()->id));
        })
            ->with(['sender', 'receiver', 'parent', 'media'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        Message::where('receiver_id', $request->user()->id)
            ->where('sender_id', $user->id)
            ->where('read', 0)
            ->update(['read' => 1, 'read_at' => now()]);

        return response()->json($messages);
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'message'     => ['required_without:attachment', 'nullable', 'string', 'max:2000'],
            'parent_id'   => ['nullable', 'exists:messages,id'],
            'attachment'  => ['nullable', 'file', 'max:10240'],
        ]);

        $authId = $request->user()->id;
        $receiverId = $request->receiver_id;

        $isBlocked = Block::where(function ($q) use ($authId, $receiverId) {
            $q->where('user_id', $authId)->where('blocked_user_id', $receiverId);
        })->orWhere(function ($q) use ($authId, $receiverId) {
            $q->where('user_id', $receiverId)->where('blocked_user_id', $authId);
        })->exists();

        if ($isBlocked) {
            return response()->json(['message' => 'You cannot send messages to this user.'], 403);
        }

        $message = Message::create([
            'sender_id'   => $authId,
            'receiver_id' => $receiverId,
            'message'     => $request->message,
            'parent_id'   => $request->parent_id,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $mime = $file->getMimeType();
            $ext  = $file->getClientOriginalExtension();

            $brand = explode('.', config('app.name', 'App'))[0];
            $type  = match (true) {
                str_contains($mime, 'image/') => 'Img',
                str_contains($mime, 'video/') => 'Vid',
                str_contains($mime, 'audio/') => 'Aud',
                $ext === 'pdf'                => 'Pdf',
                default                       => 'File',
            };

            $customName = sprintf(
                '%s_%s_%s_%s.%s',
                $brand,
                $type,
                now()->format('Ymd_Hi'),
                strtoupper(bin2hex(random_bytes(2))),
                $ext
            );

            $message->addMedia($file->getRealPath())
                ->usingFileName($customName)
                ->usingName($file->getClientOriginalName())
                ->toMediaCollection('attachments');
        }

        $receiver = User::find($receiverId);
        if ($receiver) {
            $receiver->notify(new NewMessageNotification($message, $request->user()));

            SendUserPushJob::dispatch($receiver, [
                'title' => $request->user()->name,
                'body'  => $request->message
                    ? Str::limit($request->message, 100)
                    : 'পাঠিয়েছে একটি ফাইল',
                'url'   => route('messages', ['slug' => $request->user()->slug]),
                'tag'   => 'chat-' . min($authId, $receiverId) . '-' . max($authId, $receiverId),
            ]);
        }

        $loadedMessage = $message->load(['sender', 'receiver', 'parent', 'media']);
        broadcast(new MessageSent($loadedMessage))->toOthers();

        return response()->json($loadedMessage, 201);
    }

    public function update(Request $request, Message $message)
    {
        if ($message->sender_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['message' => 'required|string|max:2000']);

        $message->update(['message' => $request->message]);
        $updatedMessage = $message->fresh(['sender', 'receiver', 'parent', 'media']);

        broadcast(new MessageUpdated($updatedMessage))->toOthers();

        return response()->json($updatedMessage);
    }

    public function destroy(Request $request, Message $message)
    {
        if (!in_array($request->user()->id, [$message->sender_id, $message->receiver_id])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messageId = $message->id;
        $receiverId = $message->receiver_id === $request->user()->id ? $message->sender_id : $message->receiver_id;

        $message->clearMediaCollection('attachments');
        $message->delete();

        broadcast(new MessageDeleted($messageId, $receiverId))->toOthers();

        return response()->json(null, 204);
    }

    public function markAsRead(Request $request, User $user)
    {
        Message::where('receiver_id', $request->user()->id)
            ->where('sender_id', $user->id)
            ->where('read', 0)
            ->update(['read' => 1, 'read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function getBlockStatus(Request $request, User $user)
    {
        $authId = $request->user()->id;

        $isBlocked = Block::where('user_id', $authId)
            ->where('blocked_user_id', $user->id)
            ->exists();

        return response()->json(['blocked' => $isBlocked]);
    }

    public function toggleBlock(Request $request, User $user)
    {
        $authId = $request->user()->id;

        $block = Block::where('user_id', $authId)
            ->where('blocked_user_id', $user->id)
            ->first();

        if ($block) {
            $block->delete();
            return response()->json(['blocked' => false, 'message' => 'Unblocked successfully']);
        }

        Block::create([
            'user_id'         => $authId,
            'blocked_user_id' => $user->id,
        ]);

        return response()->json(['blocked' => true, 'message' => 'Blocked successfully']);
    }
}