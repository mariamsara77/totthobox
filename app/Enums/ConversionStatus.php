<?php

// app/Enums/ConversionStatus.php

declare(strict_types=1);

namespace App\Enums;

enum ConversionStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'zinc',
            self::Processing => 'amber',
            self::Completed => 'lime',
            self::Failed => 'red',
        };
    }
}
