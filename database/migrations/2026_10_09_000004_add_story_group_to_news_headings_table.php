<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('news_headings', 'story_group')) {
            Schema::table('news_headings', function (Blueprint $table) {
                $table->string('story_group', 120)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('news_headings', 'story_group')) {
            Schema::table('news_headings', function (Blueprint $table) {
                $table->dropIndex(['story_group']);
                $table->dropColumn('story_group');
            });
        }
    }
};
