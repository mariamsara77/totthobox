<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('tourism_bds', function (Blueprint $table) {
        // title কলামের পরে tourism_type যোগ করার জন্য 'after' ব্যবহার করা হয়েছে
        $table->string('tourism_type')->nullable()->after('title');
    });
}

public function down(): void
{
    Schema::table('tourism_bds', function (Blueprint $table) {
        $table->dropColumn('tourism_type');
    });
}
};