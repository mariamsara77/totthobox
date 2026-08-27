<?php

namespace App\Jobs;

use App\Models\LiveChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckAndSyncStreamJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public array $backoff = [10, 30];
    public int $timeout = 60;

    public function __construct(
        public readonly string $title,
        public readonly string $url,
        public readonly bool $isExisting = false,
        public readonly string $category = 'football',
        public readonly string $countryCode = '',
        public readonly string $language = '',
        public readonly string $broadcaster = '',
        public readonly string $sourceLabel = '',
        public readonly array $m3uMeta = [],
    ) {
    }

    // ===================================================================
    //  HANDLE
    // ===================================================================
    public function handle(): void
    {
        $isLive = $this->checkStream($this->url);

        if ($this->isExisting) {
            // Existing record — only update health fields
            $channel = LiveChannel::query()->where('stream_url', $this->url)->first();
            if ($channel) {
                $isLive ? $channel->markLive() : $channel->markOffline();
            }
        } else {
            // New channel from M3U — upsert
            $channel = LiveChannel::query()->updateOrCreate(
                ['stream_url' => $this->url],
                [
                    'title' => $this->title,
                    'category' => $this->category,
                    'country_code' => strtoupper($this->countryCode) ?: null,
                    'language' => $this->language ?: null,
                    'broadcaster' => $this->broadcaster ?: null,
                    'source_label' => $this->sourceLabel ?: null,
                    'm3u_meta' => !empty($this->m3uMeta) ? $this->m3uMeta : null,
                    'last_checked_at' => now(),
                ]
            );

            $isLive ? $channel->markLive() : $channel->markOffline();
        }
    }

    // ===================================================================
    //  STREAM CHECKER — 3-step waterfall (HEAD → GET desktop → GET mobile)
    // ===================================================================
    private function checkStream(string $url): bool
    {
        if ($this->headCheck($url))
            return true;
        if ($this->getCheck($url, $this->desktopHeaders()))
            return true;
        if ($this->getCheck($url, $this->mobileHeaders()))
            return true;
        return false;
    }

    private function headCheck(string $url): bool
    {
        try {
            return Http::timeout(6)
                ->withHeaders($this->desktopHeaders())
                ->head($url)
                ->successful();
        } catch (\Exception) {
            return false;
        }
    }

    private function getCheck(string $url, array $headers): bool
    {
        try {
            $response = Http::timeout(12)
                ->withHeaders(array_merge($headers, ['Range' => 'bytes=0-8191']))
                ->withOptions([
                    'curl' => [
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_MAXREDIRS => 5,
                    ]
                ])
                ->get($url);

            if (!$response->successful() && $response->status() !== 206) {
                return false;
            }

            return $this->isValidStreamContent($url, $response->body());

        } catch (\Exception) {
            return false;
        }
    }

    // ===================================================================
    //  CONTENT VALIDATOR
    // ===================================================================
    private function isValidStreamContent(string $url, string $body): bool
    {
        $path = strtolower(parse_url($url, PHP_URL_PATH) ?? '');

        if (str_ends_with($path, '.m3u8')) {
            return str_contains($body, '#EXTM3U')
                || str_contains($body, '#EXT-X-STREAM-INF')
                || str_contains($body, '#EXT-X-TARGETDURATION')
                || str_contains($body, '#EXTINF');
        }

        if (str_ends_with($path, '.ts')) {
            return strlen($body) >= 188 && ord($body[0]) === 0x47;
        }

        if (str_ends_with($path, '.mpd')) {
            return str_contains($body, 'MPD') && str_contains($body, 'mediaPresentationDuration');
        }

        if (strlen($body) < 512)
            return false;

        $lower = strtolower(substr($body, 0, 512));

        if (
            str_contains($lower, '<!doctype html') ||
            str_contains($lower, '<html') ||
            str_contains($lower, 'access denied') ||
            str_contains($lower, 'unauthorized') ||
            str_contains($lower, 'forbidden') ||
            str_contains($lower, 'login') ||
            str_contains($lower, 'sign in')
        ) {
            return false;
        }

        return str_contains($body, '#EXTM3U')
            || str_contains($body, '#EXT-X')
            || str_contains($body, 'MPD')
            || (strlen($body) > 512 && !ctype_print(substr($body, 0, 64)));
    }

    private function desktopHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'Accept' => '*/*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept-Encoding' => 'gzip, deflate',
            'Connection' => 'keep-alive',
        ];
    }

    private function mobileHeaders(): array
    {
        return [
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Mobile Safari/537.36',
            'Accept' => '*/*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Connection' => 'keep-alive',
        ];
    }
}