<?php

namespace App\Services;

use App\Models\WhatsappCampaign;
use App\Models\WhatsappNumber;
use App\Models\WhatsappTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsappCampaignService
{
    public function getNextMessageForCampaign($campaignId)
    {
        return DB::transaction(function () use ($campaignId) {
            $campaign = WhatsappCampaign::find($campaignId);
            
            if (!$campaign || $campaign->status !== 'active') {
                Log::warning("Campaign {$campaignId} is not active");
                return null;
            }

            // পরবর্তী পেন্ডিং নম্বর তুলে আনা (Lock সহ)
            $number = $campaign->numbers()
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (!$number) {
                Log::info("No pending numbers in campaign {$campaignId}");
                return null;
            }

            // নম্বরটিকে Processing স্ট্যাটাস দেওয়া
            $number->update(['status' => 'processing']);

            // Random Template নির্বাচন
            $template = $campaign->getRandomTemplate();
            
            if (!$template) {
                Log::error("No active templates for campaign {$campaignId}");
                $number->markAsSkipped();
                return null;
            }

            // Dynamic Variables সহ Message Process করা
            $processedMessage = $template->getProcessedMessage();

            return [
                'campaign_id' => $campaignId,
                'queue_id' => $number->id,
                'phone' => $number->phone_number,
                'message' => $processedMessage,
                'image_url' => $template->image_url,
                'template_id' => $template->id,
                'min_delay' => $campaign->min_delay,
                'max_delay' => $campaign->max_delay,
            ];
        });
    }

    public function updateNumberStatus($numberId, $status, $errorMessage = null)
    {
        $number = WhatsappNumber::find($numberId);
        
        if (!$number) {
            Log::error("Number not found: {$numberId}");
            return false;
        }

        if ($status === 'sent') {
            $number->markAsSent();
        } elseif ($status === 'failed') {
            $number->markAsFailed($errorMessage);
        } elseif ($status === 'skipped') {
            $number->markAsSkipped();
        } elseif ($status === 'invalid') {
            $number->markAsInvalid();
        }

        // Campaign এর Stats আপডেট করা
        if ($number->campaign) {
            $number->campaign->updateStats();
            
            // সকল সংখ্যা প্রসেস হয়েছে কিনা চেক করা
            if ($number->campaign->pending_count === 0) {
                $number->campaign->complete();
                Log::info("Campaign {$number->campaign->id} completed");
            }
        }

        // Template এর usage count বাড়ানো
        if (isset($errorMessage) === false && $status === 'sent') {
            WhatsappTemplate::find($number->campaign?->templates()->first()?->id)?->incrementUsageCount();
        }

        return true;
    }

    public function importNumbersFromCsv($campaignId, $csvContent)
    {
        $campaign = WhatsappCampaign::find($campaignId);
        
        if (!$campaign) {
            throw new \Exception("Campaign not found");
        }

        $lines = explode("\n", trim($csvContent));
        $importedCount = 0;
        $skippedCount = 0;
        $errors = [];

        foreach ($lines as $index => $line) {
            $phone = trim($line);
            
            if (empty($phone)) {
                continue;
            }

            // ফোন নম্বর সেনিটাইজ করা
            try {
                $sanitized = WhatsappNumber::sanitizePhoneNumber($phone);
                
                // ভ্যালিডেশন
                if (!WhatsappNumber::isValidPhoneNumber($sanitized)) {
                    $skippedCount++;
                    $errors[] = "Line " . ($index + 1) . ": Invalid format - {$phone}";
                    continue;
                }

                // ডুপ্লিকেট চেক - সারা ডাটাবেসে
                $exists = WhatsappNumber::where('phone_number', $sanitized)->exists();
                if ($exists) {
                    $skippedCount++;
                    $errors[] = "Line " . ($index + 1) . ": Duplicate number - {$phone}";
                    continue;
                }

                // নম্বর সংরক্ষণ করা
                WhatsappNumber::create([
                    'phone_number' => $sanitized,
                    'status' => 'pending',
                    'campaign_id' => $campaignId,
                    'created_by' => auth()->user()->email ?? 'system',
                ]);

                $importedCount++;
            } catch (\Exception $e) {
                $skippedCount++;
                $errors[] = "Line " . ($index + 1) . ": Error - " . $e->getMessage();
            }
        }

        // Campaign stats আপডেট করা
        $campaign->update([
            'total_numbers' => $campaign->numbers()->count(),
            'pending_count' => $campaign->numbers()->pending()->count(),
        ]);

        return [
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'errors' => array_slice($errors, 0, 10), // প্রথম ১০টি error দেখানো
        ];
    }

    public function getCampaignStats($campaignId)
    {
        $campaign = WhatsappCampaign::find($campaignId);
        
        if (!$campaign) {
            return null;
        }

        return [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'status' => $campaign->status,
            'total' => $campaign->total_numbers,
            'sent' => $campaign->sent_count,
            'failed' => $campaign->failed_count,
            'skipped' => $campaign->skipped_count,
            'pending' => $campaign->pending_count,
            'progress' => $campaign->getProgress(),
            'started_at' => $campaign->started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $campaign->completed_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function getDuplicateNumbers($campaignId)
    {
        $campaign = WhatsappCampaign::find($campaignId);
        
        if (!$campaign) {
            return [];
        }

        return $campaign->numbers()
            ->select('phone_number')
            ->groupBy('phone_number')
            ->havingRaw('count(*) > 1')
            ->pluck('phone_number')
            ->toArray();
    }

    public function removeDuplicates($campaignId)
    {
        $campaign = WhatsappCampaign::find($campaignId);
        
        if (!$campaign) {
            return 0;
        }

        $duplicates = $this->getDuplicateNumbers($campaignId);
        $removedCount = 0;

        foreach ($duplicates as $phone) {
            // প্রথমটি রাখা, বাকিগুলো ডিলিট করা
            $allNumbers = $campaign->numbers()
                ->where('phone_number', $phone)
                ->orderBy('id', 'asc')
                ->get();

            if ($allNumbers->count() > 1) {
                for ($i = 1; $i < $allNumbers->count(); $i++) {
                    $allNumbers[$i]->delete();
                    $removedCount++;
                }
            }
        }

        // Stats আপডেট করা
        $campaign->update([
            'total_numbers' => $campaign->numbers()->count(),
            'pending_count' => $campaign->numbers()->pending()->count(),
        ]);

        return $removedCount;
    }
}
