<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Services\AiService;
use App\Services\VisitorTrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const USER_RATE_LIMIT = 100;
    private const USER_RATE_WINDOW = 3600; // 1 hour
    private const MAX_IMAGE_BASE64_CHARS = 6_000_000;
    private const MAX_IMAGE_BYTES = 4_500_000;

    public function __construct(
        private readonly AiService $ai,
        private readonly VisitorTrackingService $trackingService
    ) {}

    /**
     * Helper: Resolve Visitor ID from Backend Tracking Service
     */
    /**
     * Helper: Track and resolve Visitor Model Primary Key
     */
    private function resolveVisitorId(Request $request): ?int
    {
        try {
            if (! auth('sanctum')->check()) {
                $clientId = trim((string) (
                    $request->header('X-Visitor-Id')
                    ?: $request->query('visitor_id')
                    ?: $request->input('visitor_id')
                ));

                if ($clientId !== '') {
                    $visitor = $this->trackingService
                        ->getOrCreateAiChatVisitor($request);

                    return $visitor ? (int) $visitor->id : null;
                }
            }

            $visitor = $request->attributes->get('current_visitor')
                ?? $this->trackingService->getOrCreateVisitor($request);

            return $visitor ? (int) $visitor->id : null;
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve AI chat visitor', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    // =========================================================
    // GET /api/ai/sessions  (auth:sanctum OR visitor_id)
    // =========================================================
    public function sessions(Request $request)
    {
        $user = auth('sanctum')->user();
        $visitorId = $this->resolveVisitorId($request);

        $query = ChatSession::query();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($visitorId) {
            $query->where('visitor_id', $visitorId);
        } else {
            return response()->json([
                'success' => true,
                'data'    => [],
            ]);
        }

        $sessions = $query->orderByDesc('id')
            ->get(['id', 'uuid', 'title', 'created_at', 'updated_at']);

        return response()->json([
            'success' => true,
            'data'    => $sessions,
        ])->withHeaders([
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
        ]);
    }

    // =========================================================
    // GET /api/ai/sessions/{uuid}
    // =========================================================
    public function show(Request $request, string $uuid)
    {
        $user = auth('sanctum')->user();
        $visitorId = $this->resolveVisitorId($request);

        $sessionQuery = ChatSession::withTrashed()->where('uuid', $uuid);

        if ($user) {
            $sessionQuery->where('user_id', $user->id);
        } elseif ($visitorId) {
            $sessionQuery->where('visitor_id', $visitorId);
        } else {
            abort(403, 'Unauthorized');
        }

        $session = $sessionQuery->firstOrFail();

        if ($session->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'Session deleted',
            ], 410);
        }

        $messages = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'role'       => $m->role,
                'content'    => $m->content,
                'image_path' => $m->image_path
                    ? Storage::disk('public')->url($m->image_path)
                    : null,
                'created_at' => $m->created_at?->toISOString(),
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'session'  => [
                    'id'    => $session->id,
                    'uuid'  => $session->uuid,
                    'title' => $session->title,
                ],
                'messages' => $messages,
            ],
        ]);
    }

    // =========================================================
    // DELETE /api/ai/sessions/{id}
    // =========================================================
    public function destroy(Request $request, int $id)
    {
        $user = auth('sanctum')->user();
        $visitorId = $this->resolveVisitorId($request);

        $query = ChatSession::query();
        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('visitor_id', $visitorId);
        }

        $session = $query->findOrFail($id);
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session deleted successfully',
        ]);
    }

    // =========================================================
    // GET /api/ai/guest-usage
    // =========================================================
    public function guestUsage(Request $request)
    {
        $visitorId = $this->resolveVisitorId($request);
        $stats = $this->ai->getGuestUsage($visitorId ?: $request->ip());

        return response()->json([
            'success' => true,
            'data'    => $stats,
        ]);
    }

    // =========================================================
    // POST /api/ai/ask  (Guest + Auth)
    // =========================================================
    public function ask(Request $request)
    {
        $request->validate([
            'question'   => 'nullable|string|max:8000',
            'image'      => 'nullable|string|max:6000000',
            'image_mime' => 'nullable|string|in:image/jpeg,image/png,image/gif,image/webp',
            'uuid'       => 'nullable|uuid',
            'visitor_id' => 'nullable|string',
        ]);

        $content     = trim((string) $request->input('question', ''));
        $imageBase64 = $request->input('image');
        $imageMime   = $request->input('image_mime');

        // Image validation...
        if (is_string($imageBase64) && strlen($imageBase64) > self::MAX_IMAGE_BASE64_CHARS) {
            return response()->json([
                'success' => false,
                'message' => 'ছবিটি অনেক বড়। ৪.৫ MB-এর মধ্যে ছবি দিন।',
            ], 413);
        }

        if ($imageBase64) {
            $decoded = base64_decode($imageBase64, true);
            if ($decoded === false || strlen($decoded) > self::MAX_IMAGE_BYTES) {
                return response()->json([
                    'success' => false,
                    'message' => 'ছবিটি অনেক বড় বা সঠিক ফরম্যাটে নেই।',
                ], 413);
            }
        }

        if ($content === '' && ! $imageBase64) {
            return response()->json([
                'success' => false,
                'message' => 'প্রশ্ন বা ছবি দিন',
            ], 422);
        }

        $user = auth('sanctum')->user();

        if (! $user) {
            return $this->handleGuestAsk($request, $content, $imageBase64, $imageMime);
        }

        if (! $this->allowUserRequest($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'প্রতি ঘণ্টায় সর্বোচ্চ ১০০টি AI অনুরোধ করা যায়। কিছুক্ষণ পরে আবার চেষ্টা করুন।',
            ], 429, [
                'Retry-After' => (string) RateLimiter::availableIn($this->userRateKey($user->id)),
            ]);
        }

        return $this->handleAuthAsk($request, $user, $content, $imageBase64, $imageMime);
    }

    // =========================================================
    // POST /api/ai/regenerate  (auth:sanctum OR visitor)
    // =========================================================
    public function regenerate(Request $request)
    {
        $request->validate([
            'uuid' => 'required|uuid',
        ]);

        $user = auth('sanctum')->user();
        $visitorId = $this->resolveVisitorId($request);

        $sessionQuery = ChatSession::where('uuid', $request->input('uuid'));
        if ($user) {
            $sessionQuery->where('user_id', $user->id);
        } else {
            $sessionQuery->where('visitor_id', $visitorId);
        }

        $session = $sessionQuery->firstOrFail();

        $lastUser = $session->messages()
            ->where('role', 'user')
            ->latest()
            ->first();

        if (! $lastUser) {
            return response()->json([
                'success' => false,
                'message' => 'কোনো বার্তা নেই',
            ], 404);
        }

        // শেষ user message-এর পরের সব message মুছে ফেলা
        $session->messages()
            ->where('created_at', '>', $lastUser->created_at)
            ->delete();

        $context = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->slice(-12)
            ->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->toArray();

        try {
            $response = $this->ai->askAi($lastUser->content, $context);

            $aiMessage = $session->messages()->create([
                'role'    => 'model',
                'content' => $response,
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'ai_message' => [
                        'id'         => $aiMessage->id,
                        'role'       => 'model',
                        'content'    => $response,
                        'image_path' => null,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Regenerate error', [
                'message' => $e->getMessage(),
                'session' => $session->uuid,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'উত্তর পেতে সমস্যা হচ্ছে — আবার চেষ্টা করুন।',
            ], 500);
        }
    }

    // =========================================================
    // POST /api/ai/edit-regenerate  (auth:sanctum OR visitor)
    // =========================================================
    public function editAndRegenerate(Request $request)
    {
        $request->validate([
            'uuid'       => 'required|uuid',
            'message_id' => 'required|integer',
            'content'    => 'required|string|max:8000',
        ]);

        $user = auth('sanctum')->user();
        $visitorId = $this->resolveVisitorId($request);

        $sessionQuery = ChatSession::where('uuid', $request->input('uuid'));
        if ($user) {
            $sessionQuery->where('user_id', $user->id);
        } else {
            $sessionQuery->where('visitor_id', $visitorId);
        }

        $session = $sessionQuery->firstOrFail();

        $message = $session->messages()->findOrFail($request->input('message_id'));

        if ($message->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'শুধু user message edit করা যাবে',
            ], 422);
        }

        $newContent = trim($request->input('content'));
        $message->update(['content' => $newContent]);

        // পরবর্তী সব message মুছে ফেলা
        $session->messages()
            ->where('created_at', '>', $message->created_at)
            ->delete();

        $context = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->slice(-12)
            ->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->toArray();

        try {
            $response = $this->ai->askAi($newContent, $context);

            $aiMessage = $session->messages()->create([
                'role'    => 'model',
                'content' => $response,
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'user_message' => [
                        'id'      => $message->id,
                        'role'    => 'user',
                        'content' => $newContent,
                    ],
                    'ai_message' => [
                        'id'         => $aiMessage->id,
                        'role'       => 'model',
                        'content'    => $response,
                        'image_path' => null,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('EditRegenerate error', [
                'message' => $e->getMessage(),
                'session' => $session->uuid,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'উত্তর পেতে সমস্যা হচ্ছে — আবার চেষ্টা করুন।',
            ], 500);
        }
    }

    // =========================================================
    // Private Helpers
    // =========================================================

    private function userRateKey(int $userId): string
    {
        return 'ai_user:' . $userId;
    }

    private function allowUserRequest(int $userId): bool
    {
        $key = $this->userRateKey($userId);
        if (RateLimiter::tooManyAttempts($key, self::USER_RATE_LIMIT)) {
            return false;
        }
        RateLimiter::hit($key, self::USER_RATE_WINDOW);
        return true;
    }

    private function handleGuestAsk(
        Request $request,
        string $content,
        ?string $imageBase64,
        ?string $imageMime
    ) {
        $visitorId = $this->resolveVisitorId($request);

        if (! $visitorId) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor ID could not be resolved',
            ], 422);
        }

        // ২০টি লিমিট চেক
        $usage = $this->ai->getGuestUsage($visitorId);

        if (isset($usage['remaining']) && $usage['remaining'] <= 0) {
            return response()->json([
                'success'     => false,
                'message'     => 'আপনার বিনামূল্যে সীমা (২০টি মেসেজ) শেষ। আরও ব্যবহারের জন্য লগইন করুন।',
                'guest_usage' => $usage,
            ], 429);
        }

        $uuid = $request->input('uuid');
        $session = null;

        if ($uuid) {
            $session = ChatSession::where('uuid', $uuid)
                ->where('visitor_id', $visitorId)
                ->first();
        }

        $isNew = ! $session;

        if ($isNew && isset($usage['remaining']) && $usage['remaining'] <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'আপনার ফ্রি লিমিট শেষ হয়েছে। নতুন চ্যাট শুরু করতে লগইন করুন।',
            ], 429);
        }

        if ($isNew) {
            $session = ChatSession::create([
                'uuid'       => $uuid ?: (string) Str::uuid(),
                'visitor_id' => $visitorId,
                'title'      => $this->generateSessionTitle($content),
            ]);
        }

        $imageStoredPath = null;
        if ($imageBase64 && $imageMime) {
            $ext = match ($imageMime) {
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                default      => 'jpg',
            };
            $path = 'chat-images/guest/' . $visitorId . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, base64_decode($imageBase64));
            $imageStoredPath = $path;
        }

        $userMessage = $session->messages()->create([
            'role'       => 'user',
            'content'    => $content ?: '🖼️ ছবি পাঠানো হয়েছে',
            'image_path' => $imageStoredPath,
        ]);

        $context = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->slice(-12)
            ->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->toArray();

        try {
            $response = $this->ai->askAi($content, $context, $imageBase64, $imageMime, $visitorId);

            $aiMessage = $session->messages()->create([
                'role'    => 'model',
                'content' => $response,
            ]);

            $updatedUsage = $this->ai->getGuestUsage($visitorId);

            return response()->json([
                'success' => true,
                'data'    => [
                    'session' => [
                        'id'     => $session->id,
                        'uuid'   => $session->uuid,
                        'title'  => $session->title,
                        'is_new' => $isNew,
                    ],
                    'user_message' => [
                        'id'         => $userMessage->id,
                        'role'       => 'user',
                        'content'    => $userMessage->content,
                        'image_path' => $imageStoredPath
                            ? Storage::disk('public')->url($imageStoredPath)
                            : null,
                    ],
                    'ai_message' => [
                        'id'         => $aiMessage->id,
                        'role'       => 'model',
                        'content'    => $response,
                        'image_path' => null,
                    ],
                    'guest_usage' => $updatedUsage,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Guest AI error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            $userMessage->delete();

            return response()->json([
                'success' => false,
                'message' => 'উত্তর পেতে সমস্যা হচ্ছে — আবার চেষ্টা করুন।',
            ], 500);
        }
    }

    private function handleAuthAsk(
        Request $request,
        $user,
        string $content,
        ?string $imageBase64,
        ?string $imageMime
    ) {
        $uuid = $request->input('uuid');
        $session = null;

        if ($uuid) {
            $session = ChatSession::where('uuid', $uuid)
                ->where('user_id', $user->id)
                ->first();
        }

        $isNew = ! $session;

        if ($isNew) {
            $session = ChatSession::create([
                'uuid'    => $uuid ?: (string) Str::uuid(),
                'user_id' => $user->id,
                'title'   => $this->generateSessionTitle($content),
            ]);
        }

        $imageStoredPath = null;
        if ($imageBase64 && $imageMime) {
            $ext = match ($imageMime) {
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                default      => 'jpg',
            };

            $path = 'chat-images/' . $user->id . '/' . Str::uuid() . '.' . $ext;
            Storage::disk('public')->put($path, base64_decode($imageBase64));
            $imageStoredPath = $path;
        }

        $userMessage = $session->messages()->create([
            'role'       => 'user',
            'content'    => $content ?: '🖼️ ছবি পাঠানো হয়েছে',
            'image_path' => $imageStoredPath,
        ]);

        $context = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->slice(-12)
            ->map(fn($m) => [
                'role'    => $m->role,
                'content' => $m->content,
            ])
            ->values()
            ->toArray();

        try {
            $response = $this->ai->askAi($content, $context, $imageBase64, $imageMime);

            $aiMessage = $session->messages()->create([
                'role'    => 'model',
                'content' => $response,
            ]);

            return response()->json([
                'success' => true,
                'data'    => [
                    'session' => [
                        'id'     => $session->id,
                        'uuid'   => $session->uuid,
                        'title'  => $session->title,
                        'is_new' => $isNew,
                    ],
                    'user_message' => [
                        'id'         => $userMessage->id,
                        'role'       => 'user',
                        'content'    => $userMessage->content,
                        'image_path' => $imageStoredPath
                            ? Storage::disk('public')->url($imageStoredPath)
                            : null,
                    ],
                    'ai_message' => [
                        'id'         => $aiMessage->id,
                        'role'       => 'model',
                        'content'    => $response,
                        'image_path' => null,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Auth AI error', [
                'message' => $e->getMessage(),
                'session' => $session->uuid,
                'trace'   => $e->getTraceAsString(),
            ]);

            $userMessage->delete();

            return response()->json([
                'success' => false,
                'message' => 'উত্তর পেতে সমস্যা হচ্ছে — আবার চেষ্টা করুন।',
            ], 500);
        }
    }

    private function generateSessionTitle(string $content): string
    {
        $content = trim($content);
        if ($content === '') {
            return 'ছবি সহ কথোপকথন';
        }
        return Str::limit($content, 50, '...');
    }
}