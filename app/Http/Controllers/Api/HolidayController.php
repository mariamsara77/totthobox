<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class HolidayController extends Controller
{
    /**
     * ১. সব ডাটা দেখানোর জন্য (All Holidays List)
     * URL: GET /api/holidays?year=2024&month=4&type=public
     */
   /**
 * সব ছুটির তালিকা (Search + Filter + Pagination)
 * GET /api/holidays?search=...&year=2025&type=...&from=...&to=...&page=1&per_page=15
 */
public function index(Request $request)
{
    $query = Holiday::query();

    // Search
  // Search (title + slug + details)
if ($request->filled('search')) {
    $search = $request->search;
    $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('slug', 'like', "%{$search}%")
          ->orWhere('details', 'like', "%{$search}%");
    });
}

    // Year (fromDate না থাকলে)
    if ($request->filled('year') && !$request->filled('from')) {
        $query->whereYear('date', $request->year);
    }

    // Type
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }

    // Date range
    if ($request->filled('from')) {
        $query->whereDate('date', '>=', $request->from);
    }
    if ($request->filled('to')) {
        $query->whereDate('date', '<=', $request->to);
    }

    // Month (optional extra)
    if ($request->filled('month')) {
        $query->whereMonth('date', $request->month);
    }

    $perPage = $request->get('per_page', 15);

    $holidays = $query->with(['media'])->orderBy('date', 'asc')->paginate($perPage);

    $formatted = $holidays->getCollection()->map(function ($holiday) {
    $date = Carbon::parse($holiday->date);

    // Multiple images
    $mediaItems = $holiday->getMedia('holiday_images');
    if ($mediaItems->isEmpty()) {
        $mediaItems = $holiday->getMedia('default');
    }

    $images = $mediaItems->map(fn ($m) => [
        'url' => $m->getUrl(),
        'caption' => $m->name ?? null,
    ])->values()->toArray();

    return [
        'id'             => $holiday->id,
        'title'          => $holiday->title,
        'slug'           => $holiday->slug ?? (string) $holiday->id,
        'type'           => $holiday->type,
        'date'           => $date->format('Y-m-d'),
        'date_formatted' => $date->format('d F, Y'),
        'day_name_bn'    => bn_day($date->format('l')),
        'month_short'    => $date->format('M'),
        'day_numeric'    => $date->format('d'),
        'details'        => $holiday->details ? strip_tags($holiday->details) : null,
        'image_url'      => $images[0]['url'] ?? null,
        'images'         => $images,          // নতুন
    ];
});

    return response()->json([
        'success' => true,
        'data'    => $formatted,
        'meta'    => [
            'total'        => $holidays->total(),
            'per_page'     => $holidays->perPage(),
            'current_page' => $holidays->currentPage(),
            'last_page'    => $holidays->lastPage(),
            'has_more'     => $holidays->hasMorePages(),
        ],
        // Filter options (frontend-এ dropdown বানানোর জন্য)
        'filters' => [
            'years' => Holiday::selectRaw('YEAR(date) as year')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year'),
            'types' => Holiday::select('type')
                            ->distinct()
                            ->whereNotNull('type')
                            ->orderBy('type')
                            ->pluck('type'),
        ],
    ]);
}

    /**
     * ২. সিঙ্গেল ডাটা দেখানোর জন্য (Single Holiday / Show Page)
     * URL: GET /api/holidays/{slug_or_id}
     */
    public function show($slug)
{
    $holiday = Cache::remember("holiday_show_{$slug}", 3600, function () use ($slug) {
        return Holiday::with(['media'])
            ->where(function ($q) use ($slug) {
                $q->where('slug', $slug)->orWhere('id', $slug);
            })
            ->first();
    });

    if (!$holiday) {
        return response()->json([
            'success' => false,
            'message' => 'Holiday not found'
        ], 404);
    }

    // ভিউ রেকর্ড
    views($holiday)->record();

    $creators = $this->getCreators($holiday->id);
    $seo = $this->generateSingleSeoData($holiday);

    // ===== Multiple Images Support =====
    $mediaItems = $holiday->getMedia('holiday_images');

    // যদি holiday_images না থাকে তাহলে default collection থেকে নেওয়া
    if ($mediaItems->isEmpty()) {
        $mediaItems = $holiday->getMedia('default');
    }

    $images = $mediaItems->map(function ($media) {
        return [
            'url'     => $media->getUrl(),
            'caption' => $media->name ?? $media->file_name ?? null,
            'thumb'   => $media->getUrl('thumb') ?: $media->getUrl(),
        ];
    })->values()->toArray();

    // পুরনো কোডের সাথে compatibility রাখার জন্য প্রথম ছবি
    $firstImageUrl = $images[0]['url'] ?? null;

    return response()->json([
        'success' => true,
        'holiday' => [
            'id'              => $holiday->id,
            'title'           => $holiday->title,
            'slug'            => $holiday->slug ?? $holiday->id,
            'type'            => $holiday->type,
            'date'            => Carbon::parse($holiday->date)->format('Y-m-d'),
            'date_formatted'  => Carbon::parse($holiday->date)->format('d F, Y'),
            'month_short'     => Carbon::parse($holiday->date)->format('M'),
            'day_numeric'     => Carbon::parse($holiday->date)->format('d'),
            'day_name_bn'     => bn_day(Carbon::parse($holiday->date)->format('l')),
            'details'         => $holiday->details,

            // Single image (backward compatible)
            'image_url'       => $firstImageUrl,

            // Multiple images (MediaGallery-এর জন্য)
            'images'          => $images,

            // Statistics
            'views_count'     => views($holiday)->count(),
            'views_count_bn'  => bn_num(views($holiday)->count()),

            // Reactions
            'reactions' => [
    'like_count'        => $holiday->countReaction('like'),
    'dislike_count'     => $holiday->countReaction('dislike'),
    'user_has_liked'    => false, // সবসময় false — frontend আলাদা করে আনবে
    'user_has_disliked' => false,
],
        ],
        'creators' => $creators,
        'seo'      => $seo,
    ]);
}

    /**
     * ৩. রিঅ্যাকশন (Like/Dislike) দেওয়ার জন্য
     * URL: POST /api/holidays/{id}/react
     */
public function react(Request $request, int $id)
{
    $request->validate([
        'type' => 'required|in:like,dislike',
    ]);

    if (!auth('sanctum')->check()) {
        return response()->json([
            'success' => false,
            'message' => 'লগইন করা প্রয়োজন',
        ], 401);
    }

    $holiday = Holiday::findOrFail($id);
    $type = $request->type; // like বা dislike

    // Trait-এর react() method ব্যবহার (toggle + mutual exclusive)
    $holiday->react($type);
    $holiday->refresh();

    return response()->json([
        'success'        => true,
        'like_count'     => $holiday->countReaction('like'),
        'dislike_count'  => $holiday->countReaction('dislike'),
        'user_has_liked' => $holiday->hasReaction('like'),
        'user_has_disliked' => $holiday->hasReaction('dislike'),
    ]);
}

    /**
     * প্রাইভেট মেথড: কন্ট্রিবিউটরদের ডাটা বের করার জন্য
     */
    private function getCreators($holidayId)
    {
        return Cache::remember("holiday_creators_{$holidayId}", now()->addHour(), function () use ($holidayId) {
            $causerIds = Activity::query()
                ->where('subject_type', Holiday::class)
                ->where('subject_id', $holidayId)
                ->whereNotNull('causer_id')
                ->distinct()
                ->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->get()
                ->map(function ($user) {
                    return [
                        'name' => $user->name,
                        'slug' => $user->slug,
                        'avatar_url' => $user->avatar_url ?? null,
                        'profession' => $user->profession ?? 'কন্টেন্ট কন্ট্রিবিউটর',
                        'is_verified' => $user->email_verified_at ? true : false,
                        'last_active' => $user->last_active_at ? bn_num($user->last_active_at->diffForHumans()) : 'অজানা',
                    ];
                });
        });
    }

    /**
     * প্রাইভেট মেথড: সিঙ্গেল পেজের SEO ডাটা জেনারেট করার জন্য
     */
    private function generateSingleSeoData($holiday)
    {
        $parsedDate = Carbon::parse($holiday->date);
        $date = $parsedDate->format('d F Y');
        $dayName = bn_day($parsedDate->format('l'));
        $monthName = $parsedDate->translatedFormat('F');
        $year = $parsedDate->format('Y');

        $title = "{$holiday->title} – {$date} | ছুটির ক্যালেন্ডার";
        $description = "{$holiday->title} ({$date}) – বাংলাদেশের {$holiday->type} ছুটি। বিস্তারিত তারিখ, বার এবং বর্ণনা জানুন।";

        $keywordsArray = [
            $holiday->title, 
            "{$holiday->title} {$year}", 
            "{$holiday->type} ছুটি"
        ];

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => implode(', ', $keywordsArray),
        ];
    }

       // HolidayController বা যেখানে আছে

public function reactionStatus(int $id)
{
    $holiday = Holiday::findOrFail($id);

    $userHasLiked    = false;
    $userHasDisliked = false;

    if (auth('sanctum')->check()) {
        $userHasLiked    = $holiday->hasReaction('like');
        $userHasDisliked = $holiday->hasReaction('dislike');
    }

    return response()->json([
        'success'           => true,
        'user_has_liked'    => $userHasLiked,
        'user_has_disliked' => $userHasDisliked,
        'like_count'        => $holiday->countReaction('like'),
        'dislike_count'     => $holiday->countReaction('dislike'),
    ]);
}
}