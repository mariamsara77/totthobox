<?php

// app/Enums/ConversionType.php

declare(strict_types=1);

namespace App\Enums;

enum ConversionType: string
{
    case Document = 'document';
    case Media = 'media';
    case Data = 'data';
}
