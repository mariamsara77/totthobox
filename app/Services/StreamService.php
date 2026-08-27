<?php

namespace App\Services;

use App\Models\YoutubeStream;
use Illuminate\Support\Facades\Log;

class StreamService
{
    public function refreshAllStreams()
    {
        // অনেক ডাটা থাকলে মেমোরি লোড কমাতে chunk ব্যবহার করা ভালো
        YoutubeStream::chunk(100, function ($channels) {
            foreach ($channels as $channel) {
                // কমান্ড রান করার সময় কোনো এরর হলে যেন লগ ফাইলে জমা হয়
                $cmd = "/usr/local/bin/yt-dlp --user-agent 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)' -g --format 'best' " . escapeshellarg($channel->channel_url);
                $streamUrl = shell_exec($cmd);
                $streamUrl = trim($streamUrl);

                if ($streamUrl && filter_var($streamUrl, FILTER_VALIDATE_URL)) {
                    $channel->update([
                        'stream_link' => $streamUrl
                    ]);
                } else {
                    // কোনো চ্যানেলের লিংক জেনারেট না হলে তা লগে ট্র্যাক রাখা
                    Log::warning("Could not refresh stream for Channel ID: {$channel->id}");
                }
            }
        });
    }
}