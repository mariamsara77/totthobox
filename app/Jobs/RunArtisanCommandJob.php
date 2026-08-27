<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunArtisanCommandJob implements ShouldQueue
{
    use Queueable;

    // জব কতবার রিট্রাই করবে
    public $tries = 3;

    // টাইমআউট 3০ মিনিট করা হয়েছে যাতে বড় কমান্ড শেষ হতে পারে
    public $timeout = 1800;

    public function __construct(
        public string $command,
        public string $title
    ) {}

    public function handle(): void
    {
        // মেমোরি এবং টাইম লিমিট রিমুভ করা হচ্ছে
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        Log::info("Queue started: {$this->title}");

        try {
            // কমান্ড রান করার সময় ইকো আউটপুট বন্ধ রাখা ভালো
            Artisan::call($this->command);

            Log::info("Queue finished: {$this->title}");
        } catch (\Throwable $e) {
            Log::error("Queue error: {$this->title}. Error: ".$e->getMessage());
            throw $e; // রিট্রাই করার জন্য এক্সেপশন থ্রো করা জরুরি
        }
    }
}
