<?php

namespace App\Jobs;

use App\Services\VisitorTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackVisitorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(VisitorTrackingService $service)
    {
        // ব্যাকগ্রাউন্ডে ট্র্যাকিং প্রসেস রান হবে
        $service->processTracking($this->data);
    }
}