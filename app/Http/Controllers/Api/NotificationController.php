<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filter = $request->query('filter', 'all');
        $perPage = (int) $request->query('per_page', 15);
        $page = (int) $request->query('page', 1);

        $query = $filter === 'unread'
            ? $user->unreadNotifications()
            : $user->notifications();

        $paginator = $query->latest()->paginate($perPage, ['*'], 'page', $page);

        $senderIds = $paginator->getCollection()
            ->map(fn ($n) => $n->data['sender_id'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $senders = User::whereIn('id', $senderIds)
            ->with('media')
            ->get()
            ->keyBy('id');

        $items = $paginator->getCollection()->map(function (DatabaseNotification $n) use ($senders) {
            $senderId = $n->data['sender_id'] ?? null;
            $sender = $senderId ? $senders->get($senderId) : null;

            return [
                'id'            => (string) $n->id,
                'sender_name'   => $sender?->name ?? 'System',
                'sender_avatar' => $sender?->avatar_url ?? null,
                'is_online'     => $sender ? $sender->isOnline() : false,
                'display_title' => $n->data['title'] ?? ($n->data['message'] ?? 'Update'),
                'message'       => Str::limit($n->data['message'] ?? '', 70),
                'action_url'    => $n->data['action_url'] ?? '#',
                'time'          => $n->created_at->diffForHumans(short: true),
                'is_unread'     => is_null($n->read_at),
            ];
        })->values()->all(); // ← খুব জরুরি

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'has_more'     => $paginator->hasMorePages(),
            ],
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function clearAll(Request $request)
    {
        $request->user()->notifications()->delete();

        return response()->json(['success' => true]);
    }
}