<?php

declare(strict_types=1);

namespace App\Core\Recommendation;

use App\Core\Enum\RecommendationAlgorithm;

interface MovieRecommendation
{
    /**
     * @param string[] $movies
     * @return string[]
     */
    public function recommend(RecommendationAlgorithm $recommendationAlgorithm, array $movies): array;

    public function supports(RecommendationAlgorithm $recommendationAlgorithm): bool;
}
