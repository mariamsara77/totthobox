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
        Schema::create('user_reports', function (Blueprint $table) {
           $table->id();

    // কে রিপোর্ট করলো
    $table->unsignedBigInteger('reported_by');

    // কি জিনিসকে রিপোর্ট করা হলো (global polymorphic)
    $table->string('target_type');   // User / Post / Comment / Message
    $table->unsignedBigInteger('target_id');

    // Reason fields
    $table->string('reason')->nullable();
    $table->text('details')->nullable();

    // Admin workflow
    $table->string('status')->default('pending'); // pending / reviewed / resolved

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};
