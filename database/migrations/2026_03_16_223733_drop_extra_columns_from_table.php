<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('table', function (Blueprint $table) {
            // ফরেন কি চেক বন্ধ করুন
            Schema::disableForeignKeyConstraints();

            Schema::dropIfExists('buy_sell_items');
            // আপনার অন্যান্য ড্রপ কলাম বা টেবিল এখানে থাকবে

            // আবার চালু করুন
            Schema::enableForeignKeyConstraints();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table', function (Blueprint $table) {
            //
        });
    }
};
