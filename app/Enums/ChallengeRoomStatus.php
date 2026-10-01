<?php

namespace App\Enums;

enum ChallengeRoomStatus: string
{
    case Waiting = 'waiting';
    case Active = 'active';
    case Finished = 'finished';
}
