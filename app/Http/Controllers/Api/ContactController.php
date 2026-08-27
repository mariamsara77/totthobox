<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactCategory;
use App\Models\ContactNumber;
use App\Models\Division;
use App\Models\District;
use App\Models\Thana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ContactController extends Controller
{
    // GET /api/contacts/categories/{slug}
    public function category(string $slug)
    {
        $category = Cache::remember("contact:category:slug:{$slug}", now()->addDays(30), function () use ($slug) {
            return ContactCategory::where('slug', $slug)->first();
        });

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return response()->json([
            'data' => [
                'id'          => $category->id,
                'name'        => $category->name,
                'slug'        => $category->slug,
                'icon'        => $category->icon,
                'description' => $category->description,
            ],
        ]);
    }

    // GET /api/contacts?category_id=&search=&division_id=&district_id=&thana_id=&types[]=&page=&per_page=
    public function index(Request $request)
    {
        $categoryId  = (int) $request->get('category_id');
        $search      = trim((string) $request->get('search', ''));
        $divisionId  = $request->get('division_id') !== null && $request->get('division_id') !== '' ? (int) $request->get('division_id') : null;
        $districtId  = $request->get('district_id') !== null && $request->get('district_id') !== '' ? (int) $request->get('district_id') : null;
        $thanaId     = $request->get('thana_id') !== null && $request->get('thana_id') !== '' ? (int) $request->get('thana_id') : null;
        $types       = array_values(array_filter((array) $request->get('types', [])));
        $perPage     = min((int) $request->get('per_page', 15), 50);
        $page        = max((int) $request->get('page', 1), 1);

        if (!$categoryId) {
            return response()->json(['message' => 'category_id required'], 422);
        }

        $key = 'contact:list:v3:' . md5(json_encode([
            $categoryId, $search, $divisionId, $districtId, $thanaId, $types, $perPage, $page
        ]));

        $contacts = Cache::remember($key, now()->addDays(7), function () use (
            $categoryId, $search, $divisionId, $districtId, $thanaId, $types, $perPage
        ) {
            return ContactNumber::query()
                ->with(['division:id,name', 'district:id,name', 'thana:id,name'])
                ->where('contact_category_id', $categoryId)
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('address', 'like', "%{$search}%")
                            ->orWhereHas('division', fn($s) => $s->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('district', fn($s) => $s->where('name', 'like', "%{$search}%"))
                            ->orWhereHas('thana', fn($s) => $s->where('name', 'like', "%{$search}%"));
                    });
                })
                ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
                ->when($districtId, fn($q) => $q->where('district_id', $districtId))
                ->when($thanaId, fn($q) => $q->where('thana_id', $thanaId))
                ->when(!empty($types), fn($q) => $q->whereIn('type', $types))
                ->latest()
                ->paginate($perPage);
        });

        return response()->json([
            'data' => $contacts->map(fn($c) => $this->transformContact($c)),
            'meta' => [
                'current_page' => $contacts->currentPage(),
                'last_page'    => $contacts->lastPage(),
                'per_page'     => $contacts->perPage(),
                'total'        => $contacts->total(),
                'has_more'     => $contacts->hasMorePages(),
            ],
        ]);
    }

    // GET /api/contacts/divisions
    public function divisions()
    {
        $divisions = Cache::remember('contact:divisions:all', now()->addDays(30), function () {
            return Division::select('id', 'name')->orderBy('name')->get();
        });

        return response()->json(['data' => $divisions]);
    }

    // GET /api/contacts/districts?division_id=
    public function districts(Request $request)
    {
        $divisionId = (int) $request->get('division_id');
        if (!$divisionId) {
            return response()->json(['data' => []]);
        }

        $districts = Cache::remember("contact:districts:div:{$divisionId}", now()->addDays(30), function () use ($divisionId) {
            return District::where('division_id', $divisionId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });

        return response()->json(['data' => $districts]);
    }

    // GET /api/contacts/thanas?district_id=
    public function thanas(Request $request)
    {
        $districtId = (int) $request->get('district_id');
        if (!$districtId) {
            return response()->json(['data' => []]);
        }

        $thanas = Cache::remember("contact:thanas:dis:{$districtId}", now()->addDays(30), function () use ($districtId) {
            return Thana::where('district_id', $districtId)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        });

        return response()->json(['data' => $thanas]);
    }

    // GET /api/contacts/types?category_id=
    public function types(Request $request)
    {
        $categoryId = (int) $request->get('category_id');
        if (!$categoryId) {
            return response()->json(['data' => []]);
        }

        $types = Cache::remember("contact:types:cat:{$categoryId}", now()->addDays(7), function () use ($categoryId) {
            return ContactNumber::where('contact_category_id', $categoryId)
                ->whereNotNull('type')
                ->where('type', '!=', '')
                ->distinct()
                ->orderBy('type')
                ->pluck('type');
        });

        return response()->json(['data' => $types]);
    }

    private function transformContact(ContactNumber $c): array
    {
        return [
            'id'          => $c->id,
            'name'        => $c->name,
            'phone'       => $c->phone,
            'alt_phone'   => $c->alt_phone,
            'email'       => $c->email,
            'address'     => $c->address,
            'type'        => $c->type,
            'designation' => $c->designation,
            'division'    => $c->division?->name,
            'district'    => $c->district?->name,
            'thana'       => $c->thana?->name,
        ];
    }
}