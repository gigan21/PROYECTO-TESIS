<?php

declare(strict_types=1);

namespace App\Enums;

enum QuestionBlockType: string
{
    case Text = 'text';
    case Formula = 'formula';
    case Input = 'input';
}
