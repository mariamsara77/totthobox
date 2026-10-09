<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatSession;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private const USER_RATE_LIMIT = 100;
    private const USER_RATE_WINDOW = 3600;
    private const MAX_IMAGE_BASE64_CHARS = 6_000_000;
    private const MAX_IMAGE_BYTES = 4_500_000;

    public function __construct(private AiService $ai) {}

    // =========================================================
    // GET /api/ai/sessions  (auth:sanctum)
    // =========================================================
public function sessions(Request $request)
{
    // সরাসরি আইডি ফিল্টার করে শেষ ১০টি ডেটা নিয়ে আসুন
    $sessions = ChatSession::where('user_id', $request->user()->id)
        ->orderBy('id', 'desc')
        ->get(['id', 'uuid', 'title', 'created_at', 'updated_at']);

    return response()->json([
        'success' => true,
        'debug_total' => $sessions->count(), // এখানে মোট কয়টি আসছে দেখতে পাবেন
        'data'    => $sessions,
])->withHeaders([
    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    'Pragma'        => 'no-cache',
]);
}
    // =========================================================
    // GET /api/ai/sessions/{uuid}  (auth:sanctum)
    // =========================================================
    public function show(Request $request, string $uuid)
    {
        $session = ChatSession::withTrashed()
            ->where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($session->trashed()) {
            return response()->json(['success' => false, 'message' => 'Session deleted'], 410);
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
    // DELETE /api/ai/sessions/{id}  (auth:sanctum)
    // =========================================================
    public function destroy(Request $request, int $id)
    {
        $session = ChatSession::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $session->delete();

        return response()->json(['success' => true]);
    }

    // =========================================================
    // GET /api/ai/guest-usage  (public)
    // =========================================================
    public function guestUsage(Request $request)
    {
        $stats = $this->ai->getGuestUsage($request->ip());

        return response()->json(['success' => true, 'data' => $stats]);
    }

    // =========================================================
    // POST /api/ai/ask  (public — guest + auth উভয়ের জন্য)
    //
    // Route এ middleware নেই, তাই auth('sanctum')->user()
    // দিয়ে manually check করা হচ্ছে।
    // =========================================================
    public function ask(Request $request)
    {
        $request->validate([
            'question'   => 'nullable|string|max:8000',
            'image'      => 'nullable|string|max:6000000',
            'image_mime' => 'nullable|string|in:image/jpeg,image/png,image/gif,image/webp',
            'uuid'       => 'nullable|uuid',
            'history'    => 'nullable|array',
        ]);

        $content     = trim((string) $request->input('question', ''));
        $imageBase64 = $request->input('image');
        $imageMime   = $request->input('image_mime');

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

        if ($content === '' && !$imageBase64) {
            return response()->json([
                'success' => false,
                'message' => 'প্রশ্ন বা ছবি দিন',
            ], 422);
        }

        // Sanctum token দিয়ে user চেনার চেষ্টা (middleware ছাড়া)
        $user = auth('sanctum')->user();

        if (!$user) {
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
    // POST /api/ai/regenerate  (auth:sanctum)
    // =========================================================
    public function regenerate(Request $request)
    {
        $request->validate(['uuid' => 'required|uuid']);

        if (! $this->allowUserRequest($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'প্রতি ঘণ্টায় সর্বোচ্চ ১০০টি AI অনুরোধ করা যায়। কিছুক্ষণ পরে আবার চেষ্টা করুন।',
            ], 429, [
                'Retry-After' => (string) RateLimiter::availableIn($this->userRateKey($request->user()->id)),
            ]);
        }

        $uuid = $request->input('uuid'); // ✅ $request->uuid নয়

        $session = ChatSession::where('uuid', $uuid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $lastUser = $session->messages()
            ->where('role', 'user')
            ->latest()
            ->first();

        if (!$lastUser) {
            return response()->json(['success' => false, 'message' => 'কোনো বার্তা নেই'], 404);
        }

        // শেষ user message-এর পরের সব message মুছে ফেলা
        $session->messages()
            ->where('created_at', '>', $lastUser->created_at)
            ->delete();

        $context = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->slice(-12)
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();

        try {
            $response  = $this->ai->askAi($lastUser->content, $context);
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
    // POST /api/ai/edit-regenerate  (auth:sanctum)
    // =========================================================
    public function editAndRegenerate(Request $request)
    {
        $request->validate([
            'uuid'       => 'required|uuid',
            'message_id' => 'required|integer',
            'content'    => 'required|string|max:8000',
        ]);

        if (! $this->allowUserRequest($request->user()->id)) {
            return response()->json([
                'success' => false,
                'message' => 'প্রতি ঘণ্টায় সর্বোচ্চ ১০০টি AI অনুরোধ করা যায়। কিছুক্ষণ পরে আবার চেষ্টা করুন।',
            ], 429, [
                'Retry-After' => (string) RateLimiter::availableIn($this->userRateKey($request->user()->id)),
            ]);
        }

        $session = ChatSession::where('uuid', $request->input('uuid'))
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $message = $session->messages()->findOrFail($request->input('message_id'));

        if ($message->role !== 'user') {
            return response()->json([
                'success' => false,
                'message' => 'শুধু user message edit করা যাবে',
            ], 422);
        }

        $newContent = trim($request->input('content'));
        $message->update(['content' => $newContent]);

        $session->messages()
            ->where('created_at', '>', $message->created_at)
            ->delete();

        $context = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->slice(-12)
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
            ->values()
            ->toArray();

        try {
            $response  = $this->ai->askAi($newContent, $context);
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
                    'ai_message'   => [
                        'id'         => $aiMessage->id,
                        'role'       => 'model',
                        'content'    => $response,
                        'image_path' => null,
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('EditRegenerate error', ['message' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'উত্তর পেতে সমস্যা হচ্ছে — আবার চেষ্টা করুন।',
            ], 500);
        }
    }

    // =========================================================
    // Private helpers
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
        $usage = $this->ai->getGuestUsage($request->ip());

        if ($usage['remaining'] <= 0) {
            return response()->json([
                'success'     => false,
                'message'     => 'আপনার বিনামূল্যে সীমা শেষ। আরও ব্যবহারের জন্য লগইন করুন।',
                'guest_usage' => $usage,
            ], 429);
        }

        $history = $request->input('history', []);
        if (!is_array($history)) {
            $history = [];
        }

        try {
            $response = $this->ai->askAi($content, $history, $imageBase64, $imageMime, $request->ip());
            $usage    = $this->ai->getGuestUsage($request->ip());

            return response()->json([
                'success' => true,
                'data'    => [
                    'user_message' => [
                        'id'         => 'guest_' . uniqid(),
                        'role'       => 'user',
                        'content'    => $content ?: '🖼️ ছবি পাঠানো হয়েছে',
                        'image_path' => null,
                    ],
                    'ai_message'   => [
                        'id'         => 'guest_' . uniqid(),
                        'role'       => 'model',
                        'content'    => $response,
                        'image_path' => null,
                    ],
                    'guest_usage'  => $usage,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Guest AI error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

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
        $uuid    = $request->input('uuid');
        $session = null;

        if ($uuid) {
            $session = ChatSession::where('uuid', $uuid)
                ->where('user_id', $user->id)
                ->first();
        }

        $isNew = !$session;

        if ($isNew) {
            $session = ChatSession::create([
                'uuid'    => $uuid ?: (string) Str::uuid(),
                'user_id' => $user->id,
                'title'   => Str::limit($content ?: 'ছবি সহ কথোপকথন', 50),
            ]);
        }

        // ছবি সেভ করা
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

        // User message সেভ
        $userMessage = $session->messages()->create([
            'role'       => 'user',
            'content'    => $content ?: '🖼️ ছবি পাঠানো হয়েছে',
            'image_path' => $imageStoredPath,
        ]);

        // AI-এর জন্য context
        $context = $session->messages()
            ->orderBy('created_at')
            ->get()
            ->slice(-12)
            ->map(fn($m) => ['role' => $m->role, 'content' => $m->content])
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
                    'session'      => [
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
                    'ai_message'   => [
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
}