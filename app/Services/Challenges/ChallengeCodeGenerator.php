<?php

namespace App\Services\Challenges;

use App\Models\ChallengeRoom;
use Illuminate\Support\Str;

class ChallengeCodeGenerator
{
    public function generate(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (ChallengeRoom::query()->where('code', $code)->exists());

        return $code;
    }
}
