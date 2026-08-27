<?php

namespace App\Exceptions;

use Exception;

class GoogleIndexingQuotaExceededException extends Exception
{
    public function __construct(string $message = 'আজকের জন্য Google Indexing API-র দৈনিক কোটা শেষ হয়ে গেছে।')
    {
        parent::__construct($message);
    }
}