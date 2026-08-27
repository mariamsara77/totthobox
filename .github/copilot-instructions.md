# Copilot Instructions for totthobox

## Project Overview

-   **Framework:** Laravel web application with heavy use of Livewire and Volt for interactive UIs.
-   **Domain:** Bangladeshi content (holidays, calendars, info, tourism, health, etc.).
-   **Backend:** Business logic in `app/` (Controllers, Models, Livewire, Services, Helpers).
-   **Frontend:** Blade templates in `resources/views/`, with Livewire/Volt components for reactivity.
-   **Database:** SQLite by default (`database/database.sqlite`), migrations in `database/migrations/`.

## Key Patterns & Conventions

-   **Livewire/Volt Components:**
    -   Anonymous classes in Blade files (see `resources/views/livewire/global/search.blade.php`).
    -   State and actions via public properties/methods in the class block at the top of Blade files.
    -   Use `wire:model.live` and `wire:click` for reactivity. Debounced search and keyboard navigation are common.
    -   Example: See the `searchableModels` array in `search.blade.php` for how search config is structured per model.
-   **Models:**
    -   Eloquent models in `app/Models/` (e.g., `Holiday.php`, `BasicHealth.php`).
    -   Use query scopes and `when()` for dynamic filtering.
-   **Helpers:**
    -   Custom helpers for Bangla localization in `app/Helpers/` (e.g., `bn_num`, `bn_date`).
    -   Use these in views for all Bangla number/date formatting.
-   **Routing:**
    -   Route definitions in `routes/` (e.g., `web.php`).
    -   Route names are referenced in search configs for result linking.
-   **Blade UI:**
    -   Custom Blade components and `flux:` tags are used for UI elements (see search input in `search.blade.php`).
    -   Use Alpine.js for keyboard and dropdown interactivity in Livewire views.

## Developer Workflows

-   **Install dependencies:**
    -   PHP: `composer install`
    -   JS/CSS: `npm install`
-   **Build assets:**
    -   `npm run build` (Vite, see `vite.config.js`)
-   **Run dev server:**
    -   `php artisan serve` (Laravel backend)
    -   `npm run dev` (Vite frontend hot reload)
-   **Testing:**
    -   `php artisan test` or `vendor/bin/pest` (see `tests/`)
-   **Database:**
    -   Run migrations: `php artisan migrate`

## Integration & External Dependencies

-   **Livewire, Volt, Alpine.js** for frontend interactivity.
-   **Carbon** for date handling.
-   **Custom helpers** for Bangla localization.
-   **Vite** for asset pipeline.

## Examples & References

-   `resources/views/livewire/global/search.blade.php`: Advanced search config, keyboard navigation, result grouping, highlighting, and linking.
-   `resources/views/livewire/website/calendar/holiday.blade.php`: Filtering, searching, paginating holidays, modal details, Bangla date formatting.

## Special Notes

-   Always use `wire:model.live` and `wire:click` for Livewire interactivity.
-   Follow the structure of existing Volt/Livewire components for new features.
-   Use custom helpers for all Bangla localization in views.
-   For search, add new models to the `searchableModels` array with label, fields, route, and display config.

---

For unclear patterns, review referenced files or ask maintainers for clarification.
