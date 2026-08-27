<?php

namespace App\Services;

use App\Models\WhatsappNumber;
use Illuminate\Support\Facades\Log;

class WhatsappNumberService
{
    public function validateAndSanitize($phoneNumber)
    {
        try {
            $phone = trim($phoneNumber);
            
            if (empty($phone)) {
                return [
                    'valid' => false,
                    'error' => 'Phone number is empty',
                ];
            }

            // শুধুমাত্র ডিজিট রাখা
            $digitsOnly = preg_replace('/\D/', '', $phone);
            
            if (strlen($digitsOnly) < 10) {
                return [
                    'valid' => false,
                    'error' => 'Phone number too short',
                ];
            }

            if (strlen($digitsOnly) > 15) {
                return [
                    'valid' => false,
                    'error' => 'Phone number too long',
                ];
            }

            // বাংলাদেশের নম্বর (10 digits) কে 880 prefix দেওয়া
            if (strlen($digitsOnly) === 10) {
                $digitsOnly = '880' . $digitsOnly;
            }

            // +880 prefix যোগ করা
            if (!str_starts_with($digitsOnly, '880')) {
                return [
                    'valid' => false,
                    'error' => 'Invalid country code',
                ];
            }

            $sanitized = '+' . $digitsOnly;

            // Final format check: +880XXXXXXXXXX
            if (!preg_match('/^\+880\d{10}$/', $sanitized)) {
                return [
                    'valid' => false,
                    'error' => 'Invalid phone format',
                ];
            }

            return [
                'valid' => true,
                'phone' => $sanitized,
            ];
        } catch (\Exception $e) {
            Log::error("Phone validation error: " . $e->getMessage());
            return [
                'valid' => false,
                'error' => 'Validation error: ' . $e->getMessage(),
            ];
        }
    }

    public function checkDuplicateInDatabase($phoneNumber, $excludeId = null)
    {
        $query = WhatsappNumber::where('phone_number', $phoneNumber);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function bulkValidate($phoneNumbers)
    {
        $results = [
            'valid' => [],
            'invalid' => [],
            'duplicates' => [],
        ];

        $seenPhones = [];

        foreach ($phoneNumbers as $phone) {
            $validation = $this->validateAndSanitize($phone);
            
            if (!$validation['valid']) {
                $results['invalid'][] = [
                    'phone' => $phone,
                    'reason' => $validation['error'],
                ];
                continue;
            }

            $sanitized = $validation['phone'];

            // Internal duplicate check (same batch)
            if (in_array($sanitized, $seenPhones)) {
                $results['duplicates'][] = [
                    'phone' => $phone,
                    'type' => 'batch_duplicate',
                ];
                continue;
            }

            // Database duplicate check
            if ($this->checkDuplicateInDatabase($sanitized)) {
                $results['duplicates'][] = [
                    'phone' => $phone,
                    'type' => 'database_duplicate',
                ];
                continue;
            }

            $results['valid'][] = $sanitized;
            $seenPhones[] = $sanitized;
        }

        return $results;
    }
}
