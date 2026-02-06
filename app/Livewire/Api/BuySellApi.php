<?php

namespace App\Livewire\Api;

use Illuminate\Http\Request;
use App\Models\BuySellPost;

class BuySellApi
{
    public function __invoke(Request $request)
    {
        return response()->json(BuySellPost::all());
    }
}
