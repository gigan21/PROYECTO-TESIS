<?php

namespace App\Enums;

enum AttendanceRecordStatus: string
{
    case Presente = 'presente';
    case Ausente = 'ausente';
}
