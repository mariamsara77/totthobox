<?php

namespace App\Enums;

enum IndexingStatus: string
{
    case Pending = 'pending';
    case Queued = 'queued';
    case Success = 'success';
    case Failed = 'failed';
    case QuotaExceeded = 'quota_exceeded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'পেন্ডিং',
            self::Queued => 'কিউতে আছে',
            self::Success => 'সফল',
            self::Failed => 'ব্যর্থ',
            self::QuotaExceeded => 'কোটা শেষ',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'zinc',
            self::Queued => 'blue',
            self::Success => 'lime',
            self::Failed => 'red',
            self::QuotaExceeded => 'amber',
        };
    }
}