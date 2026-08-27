<?php

namespace App\Jobs;

use App\Services\WhatsappCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWhatsappCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected $campaignId)
    {
    }

    public function handle(WhatsappCampaignService $campaignService)
    {
        $payload = $campaignService->getNextMessageForCampaign($this->campaignId);

        if (!$payload) {
            return; // No pending numbers or inactive campaign
        }

        try {
            // এখানে আপনার WhatsApp Gateway API (e.g., Twilio, UltraMsg) কল হবে
            // $apiResponse = $this->sendWhatsApp($payload['phone'], $payload['message']);

            $campaignService->updateNumberStatus($payload['queue_id'], 'sent');
        } catch (\Exception $e) {
            $campaignService->updateNumberStatus($payload['queue_id'], 'failed', $e->getMessage());
        }
    }
}