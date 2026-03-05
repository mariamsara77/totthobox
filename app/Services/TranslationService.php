<?php

namespace App\Services;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected $translator;

    public function __construct()
    {
        $this->translator = new GoogleTranslate();
        $this->translator->setSource(config('translator.default'));
    }

    public function translateHtml($content, $target)
    {
        if ($target === config('app.locale'))
            return $content;

        // ১. স্ক্রিপ্ট, স্টাইল এবং ক্লাসগুলোকে অনুবাদের বাইরে রাখা (Safety First)
        // এটি লেআউট ভাঙা রোধ করবে এবং স্পিড বাড়াবে
        // TranslationService.php এর translateHtml মেথডে
        return preg_replace_callback(
            // এই Regex টি বাংলা ক্যারেক্টার দিয়ে শুরু হওয়া টেক্সট এবং তার সাথের ইংরেজি/সংখ্যা ধরবে
            '/(?<!<style|<script|<textarea|<pre|<code|<kbd)(?![^<]*>)([\x{0980}-\x{09FF}]+[\x{0980}-\x{09FF}\s\.\,\-\:\;\?\!\d\(\)\[\]]*)/u',
            function ($matches) use ($target) {
                $text = trim($matches[0]);
                // শুধু দাড়ি, কমা বা সংখ্যা থাকলে ইগনোর করবে
                if (strlen($text) < 2 || is_numeric($text) || preg_match('/^[\p{P}\s\d]+$/u', $text))
                    return $matches[0];

                return $this->translateText($text, $target);
            },
            $content
        );
    }

    public function translateText($text, $target)
    {
        if (empty($text) || is_numeric($text))
            return $text;

        $key = "trans_{$target}_" . md5($text);

        return Cache::remember($key, now()->addDays(7), function () use ($text, $target) {
            try {
                $this->translator->setTarget($target);
                return $this->translator->translate($text);
            } catch (\Exception $e) {
                Log::warning("Translation failed for: $text");
                return $text;
            }
        });
    }
}