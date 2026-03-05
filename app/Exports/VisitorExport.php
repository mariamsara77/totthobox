<?php

namespace App\Exports;

use App\Models\Visitor;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class VisitorExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        // ইগার লোডিং ব্যবহার করছি যাতে এক্সপোর্ট করার সময় বারবার ডাটাবেস কোয়েরি (N+1 problem) না হয়
        $this->query = $query->with('user');
    }

    public function query()
    {
        return $this->query;
    }

    /**
     * এক্সেল ফাইলের কলাম হেডার
     */
    public function headings(): array
    {
        return [
            'ID',
            'User Name / Hash',
            'Email',
            'Device Type',
            'Browser',
            'OS',
            'Location (City, Country)',
            'Platform',
            'First Seen',
            'Last Seen',
        ];
    }

    /**
     * প্রতিটি রো এর ডাটা ম্যাপিং
     */
    public function map($visitor): array
    {
        return [
            $visitor->id,
            // যদি ইউজার রেজিস্টার্ড থাকে তবে নাম, নাহলে হ্যাশ
            $visitor->user ? $visitor->user->name : 'Guest (' . substr($visitor->hash, 0, 8) . ')',
            $visitor->user ? $visitor->user->email : 'N/A',
            ucfirst($visitor->device_type),
            $visitor->browser_family,
            $visitor->os_family,
            // মডেলের 'locationFriendly' এক্সেসর ব্যবহার করা হয়েছে
            $visitor->location_friendly,
            // PWA কি না তা চেক করা হচ্ছে
            $visitor->is_pwa ? 'App (PWA)' : 'Web Browser',
            $visitor->first_seen_at ? $visitor->first_seen_at->format('d M Y, h:i A') : 'N/A',
            $visitor->last_seen_at ? $visitor->last_seen_at->format('d M Y, h:i A') : 'N/A',
        ];
    }
}