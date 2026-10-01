<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourismBd;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class TourismBdController extends Controller
{
    private array $typeLabels = [
        'historical'   => 'ঐতিহাসিক ও প্রত্নতাত্ত্বিক',
        'heritage'     => 'রাজপ্রাসাদ ও জমিদার বাড়ি',
        'natural'      => 'প্রাকৃতিক সৌন্দর্য',
        'waterfall'    => 'ঝর্ণা ও জলপ্রপাত',
        'beach'        => 'সমুদ্র সৈকত',
        'hill_station' => 'পাহাড় ও পার্বত্য এলাকা',
        'forest'       => 'বন ও বন্যপ্রাণী',
        'religious'    => 'ধর্মীয় ও পবিত্র স্থান',
        'cultural'     => 'সাংস্কৃতিক ও জাদুঘর',
        'adventure'    => 'অ্যাডভেঞ্চার ও ট্র্যাকিং',
        'resort'       => 'রিসোর্ট ও বিনোদন কেন্দ্র',
        'riverine'     => 'হাওর ও নদীকেন্দ্রিক',
        'picnic'       => 'পিকনিক স্পট',
    ];

    // GET /api/tourism-bd?search=&type=&division_id=&district_id=&thana_id=&page=&per_page=
    public function index(Request $request)
    {
        $search      = trim((string) $request->get('search', ''));
        $type        = $request->get('type', '');
        $divisionId  = $request->get('division_id') !== null && $request->get('division_id') !== ''
            ? (int) $request->get('division_id') : null;
        $districtId  = $request->get('district_id') !== null && $request->get('district_id') !== ''
            ? (int) $request->get('district_id') : null;
        $thanaId     = $request->get('thana_id') !== null && $request->get('thana_id') !== ''
            ? (int) $request->get('thana_id') : null;
        $perPage     = min((int) $request->get('per_page', 10), 50);

        $items = TourismBd::query()
            ->with(['division:id,name', 'district:id,name', 'thana:id,name', 'media'])
            ->where('status', 1)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                       ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($type, fn ($q) => $q->where('tourism_type', $type))
            ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
            ->when($districtId, fn ($q) => $q->where('district_id', $districtId))
            ->when($thanaId, fn ($q) => $q->where('thana_id', $thanaId))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => $items->getCollection()->map(fn ($item) => $this->transformListItem($item)),
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'per_page'     => $items->perPage(),
                'total'        => $items->total(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    // GET /api/tourism-bd/filters
    public function filters()
    {
        $divisions = Cache::remember('divisions_list_opt', 86400, function () {
            return Division::orderBy('name')->get(['id', 'name']);
        });

        $types = collect($this->typeLabels)->map(fn ($label, $value) => [
            'value' => $value,
            'label' => $label,
        ])->values();

        return response()->json([
            'divisions' => $divisions,
            'types'     => $types,
        ]);
    }

    // GET /api/tourism-bd/districts?division_id=
    public function districts(Request $request)
    {
        $divisionId = (int) $request->get('division_id');
        if (!$divisionId) {
            return response()->json(['data' => []]);
        }

        $data = Cache::remember("districts_div_{$divisionId}", 86400, function () use ($divisionId) {
            return District::where('division_id', $divisionId)
                ->orderBy('name')
                ->get(['id', 'name', 'division_id']);
        });

        return response()->json(['data' => $data]);
    }

    // GET /api/tourism-bd/thanas?district_id=
    public function thanas(Request $request)
    {
        $districtId = (int) $request->get('district_id');
        if (!$districtId) {
            return response()->json(['data' => []]);
        }

        $data = Cache::remember("thanas_dist_{$districtId}", 86400, function () use ($districtId) {
            return Thana::where('district_id', $districtId)
                ->orderBy('name')
                ->get(['id', 'name', 'district_id']);
        });

        return response()->json(['data' => $data]);
    }

    // GET /api/tourism-bd/{slug}
    public function show(string $slug)
{
    $item = TourismBd::with(['division', 'district', 'thana', 'media'])
        ->where(function ($q) use ($slug) {
            $q->where('slug', $slug)->orWhere('id', $slug);
        })
        ->where('status', 1)
        ->first();

    if (!$item) {
        return response()->json(['message' => 'Not found'], 404);
    }

    views($item)->record();

    return response()->json([
        'data' => $this->transformShowItem($item),
    ]);
}

    // GET /api/tourism-bd/creators
    public function creators()
    {
        $creators = Cache::remember('tourism_bd_contributors', now()->addHour(), function () {
            $causerIds = Activity::query()
                ->where('subject_type', TourismBd::class)
                ->whereNotNull('causer_id')
                ->distinct()
                ->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get()
                ->map(fn ($u) => $this->transformCreator($u));
        });

        return response()->json(['data' => $creators]);
    }

    // GET /api/tourism-bd/{id}/creators
    public function itemCreators(int $id)
    {
        $creators = Cache::remember("tourism_bd_creators_{$id}", now()->addHour(), function () use ($id) {
            $causerIds = Activity::query()
                ->where('subject_type', TourismBd::class)
                ->where('subject_id', $id)
                ->whereNotNull('causer_id')
                ->distinct()
                ->pluck('causer_id');

            return User::query()
                ->whereIn('id', $causerIds)
                ->select(['id', 'name', 'avatar', 'slug', 'email_verified_at', 'last_active_at', 'profession'])
                ->with(['media'])
                ->get()
                ->map(fn ($u) => $this->transformCreator($u));
        });

        return response()->json(['data' => $creators]);
    }

    // POST /api/tourism-bd/{id}/react
    public function react(Request $request, int $id)
{
    $request->validate(['type' => 'required|in:like,dislike']);

    if (!auth()->check()) {
        return response()->json([
            'success' => false,
            'message' => 'লগইন করা প্রয়োজন',
        ], 401);
    }

    $item = TourismBd::findOrFail($id);
    $item->react($request->type);
    $item->refresh();

    return response()->json([
        'success'           => true,
        'like_count'        => $item->countReaction('like'),
        'dislike_count'     => $item->countReaction('dislike'),
        'user_has_liked'    => $item->hasReaction('like'),
        'user_has_disliked' => $item->hasReaction('dislike'),
    ]);
}

public function reactionStatus(int $id)
{
    $item = TourismBd::findOrFail($id);

    $userHasLiked    = false;
    $userHasDisliked = false;

    if (auth()->check()) {
        $userHasLiked    = $item->hasReaction('like');
        $userHasDisliked = $item->hasReaction('dislike');
    }

    return response()->json([
        'success'           => true,
        'user_has_liked'    => $userHasLiked,
        'user_has_disliked' => $userHasDisliked,
        'like_count'        => $item->countReaction('like'),
        'dislike_count'     => $item->countReaction('dislike'),
    ]);
}

    // ---------- Transformers ----------

    private function transformListItem(TourismBd $item): array
{
    $type = $item->tourism_type;

    $imageUrl = $item->getFirstMediaUrl('tourism_images', 'thumb')
        ?: $item->getFirstMediaUrl('tourism_images')
        ?: $item->getFirstMediaUrl('default', 'thumb')
        ?: $item->getFirstMediaUrl('default');

    return [
        'id'           => $item->id,
        'title'        => $item->title,
        'slug'         => $item->slug,
        'tourism_type' => $type,
        'type_label'   => $this->typeLabels[$type] ?? null,
        'description'  => $item->description,
        'image_url'    => $imageUrl,
        'thana'        => $item->thana?->name,
        'district'     => $item->district?->name,
        'division'     => $item->division?->name,
    ];
}

   private function transformShowItem(TourismBd $item): array
{
    $type = $item->tourism_type;

    // সব ছবি নিয়ে array বানাও
    $images = $item->getMedia('tourism_images')->map(function ($media) {
        return [
            'url'     => $media->getUrl(),
            'caption' => $media->getCustomProperty('caption') ?? null,
            'thumb'   => $media->getUrl('thumb'),
        ];
    })->values()->toArray();

    // যদি tourism_images-এ কিছু না থাকে, default collection চেক করো
    if (empty($images)) {
        $images = $item->getMedia('default')->map(function ($media) {
            return [
                'url'     => $media->getUrl(),
                'caption' => $media->getCustomProperty('caption') ?? null,
                'thumb'   => $media->getUrl('thumb'),
            ];
        })->values()->toArray();
    }

    return [
        'id'           => $item->id,
        'title'        => $item->title,
        'slug'         => $item->slug,
        'tourism_type' => $type,
        'type_label'   => $this->typeLabels[$type] ?? null,
        'description'  => $item->description,
        
        // Single image (fallback / thumbnail এর জন্য)
        'image_url'    => $images[0]['url'] ?? null,
        
        // ✅ Multiple images (MediaGallery এর জন্য)
        'images'       => $images,
        
        'map'          => $item->map,
        'thana'        => $item->thana?->name,
        'district'     => $item->district?->name,
        'division'     => $item->division?->name,
        'views_count'  => views($item)->count(),

        'reactions' => [
            'like_count'        => $item->countReaction('like'),
            'dislike_count'     => $item->countReaction('dislike'),
            'user_has_liked'    => false,
            'user_has_disliked' => false,
        ],
    ];
}

    private function transformCreator(User $user): array
    {
        return [
            'id'             => $user->id,
            'name'           => $user->name,
            'slug'           => $user->slug,
            'avatar_url'     => $user->avatar_url ?? null,
            'profession'     => $user->profession,
            'is_verified'    => (bool) $user->email_verified_at,
            'is_online'      => method_exists($user, 'isOnline') ? $user->isOnline() : false,
            'last_active_at' => $user->last_active_at?->diffForHumans(),
        ];
    }
}