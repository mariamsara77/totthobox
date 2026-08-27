<?php

namespace App\Http\Controllers;

use App\Models\IdCard;

class IdCardController extends Controller
{
    public function show(IdCard $idCard)
    {
        abort_unless($idCard->user_id === auth()->id(), 403);

        return view('id-cards.print', [
            'card' => $idCard,
        ]);
    }

    public function print(IdCard $idCard)
    {
        abort_unless($idCard->user_id === auth()->id(), 403);

        return view('id-cards.print', [
            'card' => $idCard,
        ]);
    }
}
