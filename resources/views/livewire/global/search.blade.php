<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Lazy;

new class extends Component {
    public string $query = '';
    public bool $isOpen = false;
    public int $highlightedIndex = 0;
    public int $searchDebounce = 250;
    public array $flattenedResults = [];
    public bool $isSearching = false;
    public ?string $error = null;
    public bool $showFilters = false;
    public array $activeFilters = [];
    public array $recentSearches = [];
    public array $popularSearches = [];
    public bool $showSearchTips = false;
    public string $searchMode = 'standard'; // 'standard', 'advanced'

    protected function getSearchableModels(): array
    {
        return [
            \App\Models\User::class => [
                'label' => 'Users',
                'fields' => [
                    'name' => ['operator' => 'like', 'weight' => 3],
                    'email' => ['operator' => 'like', 'weight' => 2],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'users.show',
                'limit' => 8,
                'display' => 'name',
                'metadata' => ['email', 'created_at'],
                'badge_field' => 'role',
                'badge_colors' => [
                    'admin' => 'bg-red-400 text-red-800',
                    'user' => 'bg-blue-400 text-blue-800',
                ],
                'icon' => 'user',
                'highlight_fields' => ['name', 'email'],
            ],
            // \App\Models\Visitor::class => [
            //     'label' => 'Visitors',
            //     'fields' => [
            //         'ip_address' => ['operator' => 'like', 'weight' => 3],
            //         'country' => ['operator' => 'like', 'weight' => 2],
            //         'city' => ['operator' => 'like', 'weight' => 2],
            //         'browser' => ['operator' => 'like', 'weight' => 1],
            //         'os' => ['operator' => 'like', 'weight' => 1],
            //         'device' => ['operator' => 'like', 'weight' => 1],
            //         'id' => ['operator' => '=', 'cast' => 'integer'],
            //     ],
            //     'route' => 'admin.visitor.details',
            //     'limit' => 8,
            //     'display' => 'ip_address',
            //     'metadata' => ['country', 'city', 'last_seen_at'],
            //     'badge_field' => 'device',
            //     'badge_colors' => [
            //         'Desktop' => 'bg-blue-400 text-blue-800',
            //         'Mobile' => 'bg-green-400 text-green-800',
            //         'Tablet' => 'bg-yellow-400 text-yellow-800',
            //         'Bot' => 'bg-red-400 text-red-800',
            //         'Other' => 'bg-gray-400 text-gray-800',
            //     ],
            //     'icon' => 'globe',
            //     'highlight_fields' => ['ip_address', 'country', 'city'],
            // ],
            \App\Models\IntroBd::class => [
                'label' => 'Bangladesh Info',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'description' => ['operator' => 'like', 'weight' => 2],
                    'slug' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 2],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'status' => ['operator' => '=', 'cast' => 'integer'],
                    'view_count' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'bangladesh.introduction',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['description', 'published_at', 'view_count'],
                'badge_field' => 'status',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-red-400 text-red-800',
                ],
                'icon' => 'document-text',
                'highlight_fields' => ['title', 'description'],
            ],
            \App\Models\TourismBd::class => [
                'label' => 'Tourism Places',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'description' => ['operator' => 'like', 'weight' => 2],
                    'slug' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 2],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'view_count' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'bangladesh.tourism',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['description'],
                'badge_field' => 'is_featured',
                'badge_colors' => [
                    '1' => 'bg-yellow-400 text-yellow-800',
                    '0' => 'bg-gray-400 text-gray-800',
                ],
                'highlight_fields' => ['title', 'description'],
            ],
            \App\Models\BasicHealth::class => [
                'label' => 'Health Info',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'description' => ['operator' => 'like', 'weight' => 2],
                    'summary' => ['operator' => 'like', 'weight' => 2],
                    'type' => ['operator' => 'like', 'weight' => 2],
                    'tags' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 1],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'view_count' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'health.show',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['type', 'summary', 'published_at'],
                'badge_field' => 'is_featured',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-gray-400 text-gray-800',
                ],
                'icon' => 'heart',
                'highlight_fields' => ['title', 'description', 'summary', 'type'],
            ],
            \App\Models\BasicIslam::class => [
                'label' => 'Islamic Info',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'description' => ['operator' => 'like', 'weight' => 2],
                    'type' => ['operator' => 'like', 'weight' => 2],
                    'tags' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 1],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'view_count' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'islam.show',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['type', 'description', 'published_at'],
                'badge_field' => 'is_featured',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-gray-400 text-gray-800',
                ],
                'icon' => 'document-text',
                'highlight_fields' => ['title', 'description', 'type', 'tags'],
            ],
            \App\Models\ContactNumber::class => [
                'label' => 'Contact Numbers',
                'fields' => [
                    'name' => ['operator' => 'like', 'weight' => 3],
                    'phone' => ['operator' => 'like', 'weight' => 3],
                    'alt_phone' => ['operator' => 'like', 'weight' => 2],
                    'unit_name' => ['operator' => 'like', 'weight' => 2],
                    'designation' => ['operator' => 'like', 'weight' => 2],
                    'area' => ['operator' => 'like', 'weight' => 2],
                    'zone' => ['operator' => 'like', 'weight' => 2],
                    'location' => ['operator' => 'like', 'weight' => 2],
                    'email' => ['operator' => 'like', 'weight' => 1],
                    'address' => ['operator' => 'like', 'weight' => 1],
                    'type' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'is_active' => ['operator' => '=', 'cast' => 'boolean'],
                ],
                'route' => 'contact.show',
                'limit' => 8,
                'display' => 'name',
                'metadata' => ['phone', 'designation', 'unit_name', 'area'],
                'badge_field' => 'is_active',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-red-400 text-red-800',
                ],
                'icon' => 'phone',
                'highlight_fields' => ['name', 'phone', 'unit_name', 'designation', 'area'],
            ],
            \App\Models\EstablishmentBd::class => [
                'label' => 'Establishments',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'description' => ['operator' => 'like', 'weight' => 2],
                    'slug' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 1],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'founding_year' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'status' => ['operator' => '=', 'cast' => 'integer'],
                    'view_count' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'bangladesh.establishment',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['short_description', 'formatted_founding_year', 'published_at'],
                'badge_field' => 'status',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-red-400 text-red-800',
                ],
                'icon' => 'document-text',
                'highlight_fields' => ['title', 'description', 'establishment_type'],
            ],
            \App\Models\FoodDescribe::class => [
                'label' => 'Food Information',
                'fields' => [
                    'bangla_name' => ['operator' => 'like', 'weight' => 3],
                    'english_name' => ['operator' => 'like', 'weight' => 3],
                    'category' => ['operator' => 'like', 'weight' => 2],
                    'sub_category' => ['operator' => 'like', 'weight' => 2],
                    'description' => ['operator' => 'like', 'weight' => 2],
                    'health_benefits' => ['operator' => 'like', 'weight' => 2],
                    'nutrients' => ['operator' => 'like', 'weight' => 1],
                    'medical_info' => ['operator' => 'like', 'weight' => 1],
                    'combinations' => ['operator' => 'like', 'weight' => 1],
                    'Benefits' => ['operator' => 'like', 'weight' => 2],
                    'slug' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'food.show',
                'limit' => 8,
                'display' => 'bangla_name',
                'metadata' => ['english_name', 'category', 'health_benefits'],
                'badge_field' => 'category',
                'badge_colors' => [
                    'vegetables' => 'bg-green-400 text-green-800',
                    'fruits' => 'bg-yellow-400 text-yellow-800',
                    'meat' => 'bg-red-400 text-red-800',
                    'fish' => 'bg-blue-400 text-blue-800',
                    'spices' => 'bg-orange-400 text-orange-800',
                    'other' => 'bg-gray-400 text-gray-800',
                ],
                'icon' => 'document-text',
                'highlight_fields' => ['bangla_name', 'english_name', 'description', 'health_benefits'],
            ],
            \App\Models\Holiday::class => [
                'label' => 'Holidays',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'details' => ['operator' => 'like', 'weight' => 2],
                    'type' => ['operator' => 'like', 'weight' => 2],
                    'tags' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 1],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'is_annual' => ['operator' => '=', 'cast' => 'boolean'],
                    'view_count' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'calendar.holiday',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['type', 'date', 'details'],
                'badge_field' => 'type',
                'badge_colors' => [
                    'national' => 'bg-red-400 text-red-800',
                    'religious' => 'bg-green-400 text-green-800',
                    'international' => 'bg-blue-400 text-blue-800',
                    'observance' => 'bg-yellow-400 text-yellow-800',
                    'seasonal' => 'bg-purple-400 text-purple-800',
                ],
                'icon' => 'document-text',
                'highlight_fields' => ['title', 'details', 'details_en', 'type', 'tags'],
            ],
            \App\Models\HistoryBd::class => [
                'label' => 'Historical Events',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'description' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 1],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'status' => ['operator' => '=', 'cast' => 'integer'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'view_count' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'bangladesh.history',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['short_description', 'published_at', 'view_count'],
                'badge_field' => 'status',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-red-400 text-red-800',
                ],
                'icon' => 'document-text',
                'highlight_fields' => ['title', 'description'],
            ],
            \App\Models\Minister::class => [
                'label' => 'Ministers',
                'fields' => [
                    'name' => ['operator' => 'like', 'weight' => 3],
                    'designation' => ['operator' => 'like', 'weight' => 3],
                    'party' => ['operator' => 'like', 'weight' => 2],
                    'bio' => ['operator' => 'like', 'weight' => 2],
                    'meta_title' => ['operator' => 'like', 'weight' => 1],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'rank' => ['operator' => '=', 'cast' => 'integer'],
                    'is_current' => ['operator' => '=', 'cast' => 'boolean'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'status' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'minister.show',
                'limit' => 8,
                'display' => 'name',
                'metadata' => ['designation', 'party', 'from_date', 'to_date'],
                'badge_field' => 'is_current',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-gray-400 text-gray-800',
                ],
                'icon' => 'user',
                'highlight_fields' => ['name', 'designation', 'party', 'bio'],
            ],
            \App\Models\Test::class => [
                'label' => 'Tests',
                'fields' => [
                    'title' => ['operator' => 'like', 'weight' => 3],
                    'meta_title' => ['operator' => 'like', 'weight' => 1],
                    'meta_description' => ['operator' => 'like', 'weight' => 1],
                    'meta_keywords' => ['operator' => 'like', 'weight' => 1],
                    'slug' => ['operator' => 'like', 'weight' => 1],
                    'total_questions' => ['operator' => '=', 'cast' => 'integer'],
                    'total_marks' => ['operator' => '=', 'cast' => 'integer'],
                    'duration' => ['operator' => '=', 'cast' => 'integer'],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    'is_published' => ['operator' => '=', 'cast' => 'boolean'],
                    'is_featured' => ['operator' => '=', 'cast' => 'boolean'],
                    'status' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'test.show',
                'limit' => 8,
                'display' => 'title',
                'metadata' => ['total_questions', 'total_marks', 'duration', 'start_time', 'end_time'],
                'badge_field' => 'is_published',
                'badge_colors' => [
                    '1' => 'bg-green-400 text-green-800',
                    '0' => 'bg-red-400 text-red-800',
                ],
                'icon' => 'document-text',
                'highlight_fields' => ['title', 'meta_title'],
            ],
            \App\Models\Sign::class => [
                'label' => 'Signs',
                'fields' => [
                    'name_en' => ['operator' => 'like', 'weight' => 3],
                    'name_bn' => ['operator' => 'like', 'weight' => 3],
                    'description_en' => ['operator' => 'like', 'weight' => 2],
                    'description_bn' => ['operator' => 'like', 'weight' => 2],
                    'details' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                    // Category relationship fields
                    'category.name' => ['operator' => 'like', 'weight' => 2],
                    'category.title' => ['operator' => 'like', 'weight' => 2],
                    'category.slug' => ['operator' => 'like', 'weight' => 2],
                    'category.short_description' => ['operator' => 'like', 'weight' => 1],
                ],
                'route' => 'signs.sign',
                'route_parameters' => [
                    'slug' => 'category.slug',
                ],
                'limit' => 8,
                'display' => 'name_en',
                'metadata' => ['description_en', 'description_bn', 'category.name', 'category.slug'],
                'icon' => 'document-text',
                'highlight_fields' => ['name_en', 'name_bn', 'description_en', 'description_bn', 'category.name', 'category.slug'],
                'with' => ['category'],
            ],
            \App\Models\SignCategory::class => [
                'label' => 'Signs Categories',
                'fields' => [
                    'name' => ['operator' => 'like', 'weight' => 3],
                    'slug' => ['operator' => 'like', 'weight' => 2],
                    'title' => ['operator' => 'like', 'weight' => 2],
                    'description' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                ],
                'route' => 'signs.sign',
                'route_parameters' => [
                    'slug' => 'slug',
                ],
                'limit' => 8,
                'display' => 'name',
                'metadata' => ['description'],
                'icon' => 'document-text',
                'highlight_fields' => ['name', 'description'],
            ],

            \App\Models\Food::class => [
                'label' => 'Calorie Chart',
                'fields' => [
                    'name_en' => ['operator' => 'like', 'weight' => 3],
                    'name_bn' => ['operator' => 'like', 'weight' => 2],
                    'description' => ['operator' => 'like', 'weight' => 1],
                    'id' => ['operator' => '=', 'cast' => 'integer'],
                ],

                'relations' => [
                    'category' => [
                        'model' => \App\Models\FoodCategory::class,
                        'type' => 'belongsTo',
                        'fields' => [
                            'name_en' => ['operator' => 'like', 'weight' => 2],
                            'name_bn' => ['operator' => 'like', 'weight' => 1],
                        ],
                    ],
                    'nutrients' => [
                        'model' => \App\Models\Nutrient::class,
                        'type' => 'belongsToMany',
                        'pivot' => 'food_nutrients',
                        'fields' => [
                            'name_en' => ['operator' => 'like', 'weight' => 2],
                            'name_bn' => ['operator' => 'like', 'weight' => 1],
                        ],
                    ],
                ],

                'route' => 'health.calorie-chart',
                'limit' => 10,
                'display' => 'name_bn',

                // Metadata now includes both local + relation fields
                'metadata' => [
                    // Local food fields
                    'calorie',
                    'protein',
                    'fat',
                    'carb',
                    'fiber',
                    'serving_size',

                    // Relation fields
                    'category.name_en',
                    'category.name_bn',

                    // Nutrient & Vitamin relation names
                    'nutrients.name_en',
                    'nutrients.name_bn',
                    'vitamins.name_en',
                    'vitamins.name_bn',
                ],

                'badge_field' => 'category.name_en',
                'icon' => 'document-text',
            ],
        ];
    }

    public function mount(): void
    {
        $this->recentSearches = cache()->get('recent-searches:' . auth()->id(), []);
        $this->popularSearches = cache()->get('popular-searches', []);
    }

    public function search(): array
    {
        if (empty($this->query)) {
            return [];
        }

        $this->isSearching = true;
        $this->error = null;
        $flattenedResults = [];

        try {
            // Update recent searches
            $this->updateRecentSearches($this->query);

            // Update popular searches
            $this->updatePopularSearches($this->query);

            foreach ($this->getSearchableModels() as $modelClass => $config) {
                // Apply filters if any
                if (!empty($this->activeFilters)) {
                    if (!in_array($config['label'], $this->activeFilters)) {
                        continue;
                    }
                }

                if (!class_exists($modelClass)) {
                    continue;
                }

                $query = $modelClass::query();

                // Eager load relationships if specified
                if (!empty($config['with'])) {
                    $query->with($config['with']);
                }

                $this->buildSearchQuery($query, $config['fields'], $this->query);

                $items = $query
                    ->limit($config['limit'] ?? 8)
                    ->get()
                    ->map(function ($item) use ($config, $modelClass) {
                        return $this->formatResult($item, $config, $modelClass);
                    });

                if ($items->isNotEmpty()) {
                    $flattenedResults[] = [
                        'type' => $config['label'],
                        'icon' => $config['icon'] ?? 'heroicon-o-document-text',
                        'items' => $items->toArray(),
                        'view_all_route' => $this->getIndexRoute($config['route']),
                        'search_query' => $this->query,
                    ];
                }
            }

            usort($flattenedResults, fn($a, $b) => strcmp($a['type'], $b['type']));

            return $flattenedResults;
        } catch (\Exception $e) {
            $this->error = 'Search failed. Please try again.';
            report($e);
            return [];
        } finally {
            $this->isSearching = false;
        }
    }

    protected function updateRecentSearches(string $query): void
    {
        $userId = auth()->id();
        $key = 'recent-searches:' . $userId;

        $searches = cache()->get($key, []);

        // Remove if already exists
        $searches = array_filter($searches, fn($item) => $item['query'] !== $query);

        // Add to beginning
        array_unshift($searches, [
            'query' => $query,
            'timestamp' => now()->timestamp,
            'date' => now()->toDateTimeString(),
        ]);

        // Keep only last 5
        $searches = array_slice($searches, 0, 5);

        cache()->put($key, $searches, now()->addDays(30));
        $this->recentSearches = $searches;
    }

    protected function updatePopularSearches(string $query): void
    {
        $key = 'popular-searches';
        $searches = cache()->get($key, []);

        if (isset($searches[$query])) {
            $searches[$query]['count']++;
        } else {
            $searches[$query] = [
                'query' => $query,
                'count' => 1,
                'last_searched' => now()->timestamp,
            ];
        }

        // Sort by count and keep top 10
        uasort($searches, fn($a, $b) => $b['count'] <=> $a['count']);
        $searches = array_slice($searches, 0, 10, true);

        cache()->put($key, $searches, now()->addDays(30));
        $this->popularSearches = array_values($searches);
    }

    protected function buildSearchQuery($query, array $fields, string $searchTerm): void
    {
        $query->where(function ($q) use ($fields, $searchTerm) {
            foreach ($fields as $field => $options) {
                $operator = $options['operator'] ?? 'like';
                $value = $operator === 'like' ? "%{$searchTerm}%" : $searchTerm;

                // Handle relationship fields (fields with dots)
                if (str_contains($field, '.')) {
                    $this->buildRelationshipSearch($q, $field, $operator, $value, $options);
                } else {
                    $this->buildDirectFieldSearch($q, $field, $operator, $value, $options);
                }
            }
        });
    }

    protected function buildRelationshipSearch($query, string $field, string $operator, $value, array $options): void
    {
        $parts = explode('.', $field);
        $relationField = array_pop($parts);
        $relationPath = implode('.', $parts);

        $weight = $options['weight'] ?? 1;

        for ($i = 0; $i < $weight; $i++) {
            $query->orWhereHas($relationPath, function ($q) use ($relationField, $operator, $value, $options) {
                // Handle casting for relationship fields
                if (isset($options['cast'])) {
                    if ($options['cast'] === 'integer' && !is_numeric($value)) {
                        return;
                    }
                    if ($options['cast'] === 'boolean' && !in_array(strtolower($value), ['1', '0', 'true', 'false', 'yes', 'no'], true)) {
                        return;
                    }
                }
                $q->where($relationField, $operator, $value);
            });
        }
    }

    protected function buildDirectFieldSearch($query, string $field, string $operator, $value, array $options): void
    {
        $weight = $options['weight'] ?? 1;

        // Type casting checks
        if (isset($options['cast'])) {
            if ($options['cast'] === 'integer') {
                if (!is_numeric($value)) {
                    return;
                }
                $value = (int) $value;
            }

            if ($options['cast'] === 'boolean') {
                if (!in_array(strtolower($value), ['1', '0', 'true', 'false', 'yes', 'no'], true)) {
                    return;
                }
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            }
        }

        for ($i = 0; $i < $weight; $i++) {
            $query->orWhere($field, $operator, $value);
        }
    }

    protected function formatResult($item, array $config, string $modelClass): array
    {
        try {
            $routeParams = $this->getRouteParameters($item, $config);
            $metadata = [];
            $badge = null;

            foreach ($config['metadata'] ?? [] as $field) {
                try {
                    $value = data_get($item, $field);
                    if ($value !== null) {
                        $formattedValue = $this->formatFieldValue($field, $value);
                        $metadata[$field] = (string) $this->highlightMatches($formattedValue, $this->query);
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (isset($config['badge_field'])) {
                $badgeValue = data_get($item, $config['badge_field']);
                if ($badgeValue !== null) {
                    $badgeClass = $config['badge_colors'][$badgeValue] ?? 'bg-gray-100 text-gray-800';
                    $badge = [
                        'value' => $badgeValue,
                        'class' => $badgeClass,
                    ];
                }
            }

            $displayValue = data_get($item, $config['display']);
            $highlightedDisplay = in_array($config['display'], $config['highlight_fields'] ?? []) ? (string) $this->highlightMatches($displayValue, $this->query) : $displayValue;

            return [
                'id' => $item->getKey(),
                'display' => $displayValue,
                'highlighted_display' => $highlightedDisplay,
                'route' => route($config['route'], $routeParams),
                'metadata' => $metadata,
                'badge' => $badge,
                'raw' => $item->toArray(),
                'model_class' => $modelClass,
                'search_query' => $this->query,
            ];
        } catch (\Exception $e) {
            return [
                'id' => $item->getKey(),
                'display' => 'Item #' . $item->getKey(),
                'highlighted_display' => 'Item #' . $item->getKey(),
                'route' => '#',
                'metadata' => [],
                'badge' => null,
                'raw' => [],
                'model_class' => $modelClass,
                'search_query' => $this->query,
            ];
        }
    }

    protected function getRouteParameters($model, array $config): array
    {
        $params = [];

        // Use custom route_parameters if defined
        if (!empty($config['route_parameters'])) {
            foreach ($config['route_parameters'] as $paramName => $modelField) {
                $params[$paramName] = data_get($model, $modelField);
            }
            return $params;
        }

        // Fallback: try to get parameters from route binding
        $routeName = $config['route'];
        if (!Route::has($routeName)) {
            return [];
        }

        try {
            $route = Route::getRoutes()->getByName($routeName);
            foreach ($route->parameterNames() as $paramName) {
                $field = $route->bindingFieldFor($paramName) ?? $paramName;
                $params[$paramName] = $model->{$field} ?? $model->{$model->getRouteKeyName()};
            }
            return $params;
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function highlightMatches(string $text, string $query): HtmlString
    {
        if (empty($query)) {
            return new HtmlString(e($text));
        }

        $pattern = '/(' . preg_quote($query, '/') . ')/i';
        $replacement = '<mark class="bg-yellow-200 dark:bg-yellow-600">$1</mark>';

        return new HtmlString(preg_replace($pattern, $replacement, e($text)));
    }

    protected function formatFieldValue(string $field, $value): string
    {
        if ($value instanceof \DateTime) {
            return $value->format('M d, Y');
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (is_array($value) || $value instanceof \Illuminate\Support\Collection) {
            return implode(', ', (array) $value);
        }

        return (string) $value;
    }

    protected function getIndexRoute(string $routeName): ?string
    {
        $base = Str::beforeLast($routeName, '.');
        $variations = ["{$base}.index", "{$base}.list", $base, Str::plural($base) . '.index'];

        foreach ($variations as $variation) {
            if (Route::has($variation)) {
                return route($variation, ['q' => $this->query]);
            }
        }

        return null;
    }

    public function updatedQuery(): void
    {
        $this->isOpen = !empty($this->query);
        $this->resetHighlight();
        $this->flattenedResults = $this->search();
    }

    public function resetSearch(): void
    {
        $this->reset(['query', 'isOpen', 'highlightedIndex', 'flattenedResults']);
    }

    public function selectResult(): mixed
    {
        if (!isset($this->flattenedResults[$this->highlightedIndex])) {
            return null;
        }

        $result = $this->flattenedResults[$this->highlightedIndex];

        session()->put('search_context', [
            'query' => $this->query,
            'model_class' => $result['model_class'],
            'item_id' => $result['id'],
            'timestamp' => now()->timestamp,
        ]);

        return redirect()->to($result['route'] . '?from_search=' . now()->timestamp);
    }

    public function incrementHighlight(): void
    {
        if ($this->highlightedIndex < count($this->flattenedResults) - 1) {
            $this->highlightedIndex++;
        }
    }

    public function decrementHighlight(): void
    {
        if ($this->highlightedIndex > 0) {
            $this->highlightedIndex--;
        }
    }

    public function resetHighlight(): void
    {
        $this->highlightedIndex = 0;
    }

    public function toggleFilter(string $filter): void
    {
        if (in_array($filter, $this->activeFilters)) {
            $this->activeFilters = array_diff($this->activeFilters, [$filter]);
        } else {
            $this->activeFilters[] = $filter;
        }

        $this->flattenedResults = $this->search();
    }

    public function clearFilters(): void
    {
        $this->activeFilters = [];
        $this->flattenedResults = $this->search();
    }

    public function setSearchMode(string $mode): void
    {
        $this->searchMode = $mode;
        $this->showSearchTips = false;
    }

    public function useRecentSearch(string $query): void
    {
        $this->query = $query;
        $this->isOpen = true;
        $this->flattenedResults = $this->search();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="max-w-2xl mx-auto">
            <div class="relative">
                <div class="flex items-center h-12 px-4 backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700">
                    <div class="flex-shrink-0 ml-1 mr-3">
                        <div class="h-5 w-5 backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-md animate-pulse"></div>
                    </div>
                    <div class="flex-1 h-6 backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded animate-pulse"></div>
                    <div class="flex-shrink-0 ml-3">
                        <div class="h-6 w-16 backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-md animate-pulse"></div>
                    </div>
                </div>
            </div>
        </div>
        HTML;
    }
}; ?>

<section>
    <div x-data="{
        open: @entangle('isOpen'),
        query: @entangle('query'),
        highlightedIndex: @entangle('highlightedIndex'),
        showFilters: @entangle('showFilters'),
        handleKeydown(event) {
            if (event.key === 'ArrowUp') {
                event.preventDefault();
                @this.decrementHighlight();
                this.scrollIntoView();
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                @this.incrementHighlight();
                this.scrollIntoView();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                @this.selectResult();
            } else if (event.key === 'Escape') {
                if (this.open) {
                    this.open = false;
                } else if (this.showFilters) {
                    this.showFilters = false;
                }
            } else if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
                event.preventDefault();
                this.open = true;
                $nextTick(() => {
                    const input = $el.querySelector('input');
                    if (input) input.focus();
                });
            } else if ((event.metaKey || event.ctrlKey) && event.shiftKey && event.key === 'f') {
                event.preventDefault();
                this.showFilters = !this.showFilters;
            }
        },
        scrollIntoView() {
            const el = this.$refs.resultsContainer?.querySelector(`[data-index='${this.highlightedIndex}']`);
            if (el) {
                el.scrollIntoView({ block: 'nearest' });
            }
        }
    }" @keydown.window="handleKeydown" @click.outside="if (open && !showFilters) open = false"
        class="relative max-w-2xl mx-auto z-50">
        <!-- Search Input -->
        <div class="relative">


            <!-- Search Input -->
            <flux:input type="text" kbd="⌘K" icon="magnifying-glass" placeholder="Search..."
                wire:model.live.debounce.300ms="query" clearable autofocus
                class="backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-lg">
                <x-slot name="iconTrailing">
                    <flux:button size="sm" @click="showFilters = !showFilters" variant="subtle" class="-mr-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </flux:button>
                </x-slot>
            </flux:input>


            <!-- Filters Panel -->
            <div x-show="showFilters" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform -translate-y-2"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform -translate-y-2"
                class="absolute top-full mt-2 w-full backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50 rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 p-4 z-50">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by content type</h3>
                    <flux:button @click="showFilters = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </flux:button>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach (array_unique(array_column($this->getSearchableModels(), 'label')) as $filter)
                        <button wire:click="toggleFilter('{{ $filter }}')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex items-center"
                            :class="{{ in_array($filter, $activeFilters)
                                ? "'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400 border border-primary-200 dark:border-primary-700'"
                                : "'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600 border border-transparent'" }}">
                            {{ $filter }}
                            @if (in_array($filter, $activeFilters))
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            @endif
                        </button>
                    @endforeach

                    @if (count($activeFilters) > 0)
                        <button wire:click="clearFilters"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600 transition-colors">
                            Clear all
                        </button>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-zinc-700">
                    <div class="flex items-center space-x-2">
                        <button wire:click="setSearchMode('standard')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex-1 text-center"
                            :class="{{ $searchMode === 'standard'
                                ? "'bg-primary-600'"
                                : "'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600'" }}">
                            Standard Search
                        </button>
                        <button wire:click="setSearchMode('advanced')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors flex-1 text-center"
                            :class="{{ $searchMode === 'advanced'
                                ? "'bg-primary-600'"
                                : "'bg-gray-100 text-gray-700 dark:bg-zinc-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-600'" }}">
                            Advanced Search
                        </button>
                    </div>

                    @if ($searchMode === 'advanced')
                        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            <p>Use operators like <kbd
                                    class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">"quotes"</kbd> for exact
                                matches, <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">OR</kbd> for
                                alternatives, and <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">-</kbd>
                                to exclude terms.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Results Dropdown -->
        <div x-show="open && query.length > 0" x-transition.opacity.duration.200ms
            class="absolute z-40 mt-2 w-full backdrop-blur-xl bg-zinc-100/50 dark:bg-zinc-800/50 rounded-xl shadow-lg border border-gray-200 dark:border-zinc-700 overflow-hidden"
            id="search-results" role="listbox" x-ref="dropdown">

            @if ($error)
                <!-- Error State -->
                <div class="p-4 text-center text-red-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <p class="mt-2 text-sm">{{ $error }}</p>
                </div>
            @elseif(empty($flattenedResults) && $query)
                <!-- No Results / Suggestions -->
                <div class="p-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No results found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try different search terms or check your
                        filters</p>

                    @if (count($recentSearches) > 0)
                        <div class="mt-6 text-left">
                            <h4
                                class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                Recent searches</h4>
                            <div class="space-y-1">
                                @foreach ($recentSearches as $recent)
                                    <button wire:click="useRecentSearch('{{ $recent['query'] }}')"
                                        class="w-full text-left px-3 py-2 text-sm rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors flex items-center justify-between group">
                                        <span
                                            class="text-gray-700 dark:text-gray-300 group-hover:text-primary-600 dark:group-hover:text-primary-400">{{ $recent['query'] }}</span>
                                        <span
                                            class="text-xs text-gray-400">{{ Carbon::parse($recent['timestamp'])->diffForHumans() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-zinc-700">
                        <button wire:click="$toggle('showSearchTips')"
                            class="text-sm text-primary-600 dark:text-primary-400 hover:underline flex items-center justify-center mx-auto">
                            Search tips
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        @if ($showSearchTips)
                            <div class="mt-3 text-xs text-left text-gray-500 dark:text-gray-400 space-y-2">
                                <p>• Try different keywords or more general terms</p>
                                <p>• Check your spelling</p>
                                <p>• Use filters to narrow down results</p>
                                <p>• Use quotes for exact phrase matches: <code>"exact phrase"</code></p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <!-- Results Container -->
                <div class="backdrop-blur-lg bg-zinc-100/25 dark:bg-zinc-700/50" x-ref="resultsContainer">
                    <!-- Results Summary -->
                    <div class=" px-4 py-2.5 border-b border-gray-100 dark:border-zinc-700">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Found {{ count($flattenedResults) }} result types with
                                {{ array_sum(array_map(fn($group) => count($group['items']), $flattenedResults)) }}
                                items
                            </p>

                            @if (count($activeFilters) > 0)
                                <button wire:click="clearFilters"
                                    class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                                    Clear {{ count($activeFilters) }} filter(s)
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="max-h-[50vh] overflow-y-auto">
                        @foreach ($flattenedResults as $groupIndex => $group)
                            <div>
                                <div
                                    class="top-[46px] px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center border-b border-gray-100 dark:border-zinc-700">
                                    @switch($group['icon'])
                                        @case('user')
                                            <flux:icon.user class="h-4 w-4 mr-2 text-gray-500"/>
                                        @break

                                        @case('globe')
                                            <flux:icon.globe-alt class="h-4 w-4 mr-2 text-gray-500"/>
                                        @break

                                        @case('document-text')
                                           <flux:icon.document-text class="h-4 w-4 mr-2 text-gray-500"/>
                                        @break

                                        @default
                                            <flux:icon.user class="h-4 w-4 mr-2 text-gray-500"/>
                                    @endswitch
                                    <span>{{ $group['type'] }}</span>
                                    <span class="ml-2 text-xs text-gray-400  rounded-full px-2 py-0.5">
                                        {{ count($group['items']) }}
                                    </span>

                                    @if ($group['view_all_route'])
                                        <a href="{{ $group['view_all_route'] }}"
                                            class="ml-auto text-xs text-primary-600 dark:text-primary-400 hover:underline flex items-center"
                                            wire:navigate.hover>
                                            View all
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-0.5"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    @endif
                                </div>

                                @foreach ($group['items'] as $itemIndex => $item)
                                    @php
                                        $absoluteIndex = $loop->parent->index * count($group['items']) + $loop->index;
                                    @endphp
                                    <a href="{{ $item['route'] }}" wire:navigate
                                        class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-zinc-700/50 transition-colors border-b border-gray-100 dark:border-zinc-700 last:border-b-0
                              {{ $highlightedIndex === $absoluteIndex ? 'bg-primary-50 dark:bg-primary-900/20' : '' }}"
                                        data-index="{{ $absoluteIndex }}" role="option"
                                        :aria-selected="{{ $highlightedIndex === $absoluteIndex ? 'true' : 'false' }}">
                                        <div class="flex items-start gap-3">
                                            <!-- Icon/Avatar -->
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center mt-0.5">
                                                @if (isset($item['raw']['avatar']))
                                                    <img src="{{ Storage::url($item['raw']['avatar']) }}"
                                                        class="h-full w-full rounded-lg object-cover" alt="">
                                                @elseif(isset($item['raw']['photo']))
                                                    <img src="{{ Storage::url($item['raw']['photo']) }}"
                                                        class="h-full w-full rounded-lg object-cover" alt="">
                                                @elseif(isset($item['raw']['image']))
                                                    <img src="{{ Storage::url($item['raw']['image']) }}"
                                                        class="h-full w-full rounded-lg object-cover" alt="">
                                                @else
                                                    <span
                                                        class="text-gray-500 dark:text-gray-400 font-medium uppercase text-sm h-full w-full flex items-center justify-center bg-gray-300 dark:bg-zinc-600 rounded-lg">

                                                        {{ Str::substr($item['display'], 0, 1) }}
                                                    </span>
                                                @endif
                                            </div>

                                            <!-- Main Content -->
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <p
                                                        class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        {!! $item['highlighted_display'] !!}
                                                    </p>
                                                    @if (isset($item['badge']))
                                                        <span
                                                            class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $item['badge']['class'] }}">
                                                            {{ $item['badge']['value'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Metadata -->
                                                <div class="mt-1.5 space-y-1">
                                                    @foreach ($item['metadata'] as $field => $value)
                                                        <div
                                                            class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">
                                                            <span
                                                                class="font-medium capitalize">{{ Str::headline($field) }}:</span>
                                                            {!! $value !!}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <!-- Quick Action -->
                                            <div
                                                class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <!-- Search Tips Footer -->
                    <div class="border-t border-gray-100 dark:border-zinc-700 p-3 z-10">
                        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center space-x-4">
                                <span class="flex items-center">
                                    <kbd
                                        class="px-1.5 py-0.5 rounded border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mr-1">↑↓</kbd>
                                    Navigate
                                </span>
                                <span class="flex items-center">
                                    <kbd
                                        class="px-1.5 py-0.5 rounded border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mr-1">↵</kbd>
                                    Select
                                </span>
                                <span class="flex items-center">
                                    <kbd
                                        class="px-1.5 py-0.5 rounded border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 mr-1">Esc</kbd>
                                    Close
                                </span>
                            </div>

                            <button wire:click="$toggle('showSearchTips')"
                                class="text-primary-600 dark:text-primary-400 hover:underline">
                                Search help
                            </button>
                        </div>

                        @if ($showSearchTips)
                            <div
                                class="mt-2 pt-2 border-t border-gray-200 dark:border-zinc-700 text-xs text-gray-500 dark:text-gray-400 space-y-1">
                                <p>• Use <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">Ctrl+F</kbd> /
                                    <kbd class="px-1 py-0.5 rounded bg-gray-200 dark:bg-zinc-700">Cmd+F</kbd> to open
                                    filters
                                </p>
                                <p>• Try different keywords or check your spelling</p>
                                <p>• Use quotes for exact matches: <code>"exact phrase"</code></p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
