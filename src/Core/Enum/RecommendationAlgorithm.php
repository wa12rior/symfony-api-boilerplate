<?php

declare(strict_types=1);

namespace App\Core\Enum;

enum RecommendationAlgorithm: string
{
    case RANDOM_THREE = 'RANDOM_THREE';
    case W_EVEN_LETTER_COUNT = 'W_EVEN_LETTER_COUNT';

    case MORE_THAN_ONE_WORD = 'MORE_THAN_ONE_WORD';
}
