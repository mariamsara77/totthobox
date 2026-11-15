<?php

namespace App\Services;

use App\Models\WebsitePage;
use Illuminate\Support\Str;

class KnowledgeBaseService
{
    /**
     * ইউজারের প্রশ্ন অনুযায়ী প্রাসঙ্গিক পেজ খুঁজে AI-এর জন্য context তৈরি করে
     */
    public function getRelevantContext(string $question, int $limit = 6): string
    {
        $question = trim($question);

        if ($question === '') {
            return '';
        }

        // প্রশ্ন থেকে গুরুত্বপূর্ণ শব্দ বের করা
        $keywords = $this->extractKeywords($question);

        if (empty($keywords)) {
            return '';
        }

        // ডাটাবেস থেকে প্রাসঙ্গিক পেজ খোঁজা
        $pages = WebsitePage::query()
            ->where(function ($query) use ($keywords, $question) {
                // Full-text search (সবচেয়ে ভালো)
                $query->whereFullText(['title', 'content'], $question);

                // ফলব্যাক: keyword দিয়ে LIKE
                foreach ($keywords as $word) {
                    $query->orWhere('title', 'like', "%{$word}%")
                        ->orWhere('content', 'like', "%{$word}%");
                }
            })
            ->orderByDesc('last_crawled_at')
            ->limit($limit)
            ->get();

        if ($pages->isEmpty()) {
            return "এই বিষয়ে আমার ওয়েবসাইটে সরাসরি তথ্য পাওয়া যায়নি।";
        }

        // AI-এর জন্য সুন্দর context তৈরি
        $context = "নিচের তথ্যগুলো TotthoBox ওয়েবসাইট (https://totthobox.com) থেকে নেওয়া হয়েছে। শুধুমাত্র এই তথ্যের ভিত্তিতে উত্তর দাও। উত্তরে অবশ্যই সোর্স লিংক দাও।\n\n";

        foreach ($pages as $index => $page) {
            $number = $index + 1;

            $context .= "=== সোর্স {$number} ===\n";
            $context .= "টাইটেল: {$page->title}\n";
            $context .= "লিংক: {$page->url}\n";
            $context .= "কনটেন্ট: " . Str::limit($page->content ?? '', 1600) . "\n";

            // ইমেজ থাকলে
            if (!empty($page->images) && is_array($page->images)) {
                $context .= "ইমেজসমূহ:\n";
                foreach (array_slice($page->images, 0, 4) as $img) {
                    $alt = $img['alt'] ?? '';
                    $context .= "- {$img['url']}" . ($alt ? " (alt: {$alt})" : "") . "\n";
                }
            }

            $context .= "\n";
        }

        $context .= "উত্তর দেওয়ার নিয়ম:\n";
        $context .= "1. শুধু উপরের তথ্য ব্যবহার করো\n";
        $context .= "2. প্রতিটি গুরুত্বপূর্ণ তথ্যের সাথে সোর্স লিংক দাও\n";
        $context .= "3. ইমেজ থাকলে ইমেজ লিংকও দাও\n";
        $context .= "4. তথ্য না থাকলে স্পষ্ট করে বলো যে সাইটে এই তথ্য নেই\n";

        return $context;
    }

    /**
     * প্রশ্ন থেকে গুরুত্বপূর্ণ বাংলা/ইংরেজি শব্দ বের করা
     */
    private function extractKeywords(string $question): array
    {
        // ছোট ও অপ্রয়োজনীয় শব্দ বাদ
        $stopWords = [
            'কি',
            'কী',
            'কে',
            'কোন',
            'কোনো',
            'এবং',
            'বা',
            'আর',
            'যে',
            'যা',
            'এই',
            'সেই',
            'আমার',
            'আমি',
            'তুমি',
            'আপনি',
            'করে',
            'করা',
            'হয়',
            'হবে',
            'থেকে',
            'জন্য',
            'the',
            'is',
            'are',
            'what',
            'how',
            'when',
            'where',
            'which',
            'a',
            'an',
            'of',
            'in',
            'on',
            'to'
        ];

        $words = preg_split('/[\s\p{P}]+/u', Str::lower($question), -1, PREG_SPLIT_NO_EMPTY);

        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return mb_strlen($word) >= 3 && !in_array($word, $stopWords);
        });

        return array_values(array_unique(array_slice($keywords, 0, 10)));
    }
}