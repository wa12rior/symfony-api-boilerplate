<?php

declare(strict_types=1);

namespace App\Core\DTO\Request;

use App\Core\Enum\RecommendationAlgorithm;

final class RecommendationRequest
{
    public function __construct(
        public readonly ?RecommendationAlgorithm $recommendationAlgorithm = RecommendationAlgorithm::RANDOM_THREE,
    ) {
    }
}
