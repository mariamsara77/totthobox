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
        $this->translator->setSource(config('translator.default', 'bn'));
    }

    public function translateHtml(string $content, string $target): string
    {
        // Avoid translating technical tags, scripts, and Flux UI components
        $pattern = '/(?<!<style|<script|<textarea|<pre|<code|<kbd|<flux)(?![^<]*>)([^<>\n\r]+)/u';

        return preg_replace_callback($pattern, function ($matches) use ($target) {
            $text = trim($matches[0]);

            // Skip empty, numeric, or single characters
            if (mb_strlen($text) < 2 || is_numeric($text)) {
                return $matches[0];
            }return $this->translateText(
                $text,
                $target
            );
        }, $content);
    }

    public function translateText(string $text, string $target): string
    {
        $key = "trans_{$target}_" . md5($text);

        return Cache::remember($key, now()->addDays(30), function () use ($text, $target) {
            try {
                $this->translator->setTarget($target);
                return $this->translator->translate($text);
            } catch (\Exception $e) {
                Log::warning("Translation failed: " . $e->getMessage());
                return $text;
            }
        });
    }
}