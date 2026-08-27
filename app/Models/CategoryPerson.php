<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CategoryPerson extends Pivot
{
    /**
     * টেবিলের নাম ডিফাইন করা। 
     * লারাভেল ডিফল্টভাবে 'category_person' খুঁজবে।
     */
    protected $table = 'category_person';

    /**
     * যদি আপনার পিভট টেবিলে timestamps (created_at, updated_at) না থাকে, 
     * তবে এটি false করে দিন।
     */
    public $timestamps = false;

    /**
     * রিলেশনশিপ (ঐচ্ছিক): যদি সরাসরি পিভট থেকে ডেটা কল করতে চান।
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function category()
    {
        return $this->belongsTo(PeopleCategory::class, 'category_id');
    }
}