<?php

declare(strict_types=1);

namespace App\Core\Recommendation;

use App\Core\Enum\RecommendationAlgorithm;

final class MoreThanOneWordRecommendation implements MovieRecommendation
{
    public function recommend(RecommendationAlgorithm $recommendationAlgorithm, array $movies): array
    {
        $results = [];

        foreach ($movies as $movie) {
            if (!preg_match('/\s/', trim($movie))) {
                continue;
            }

            $results[] = $movie;
        }

        return $results;
    }

    public function supports(RecommendationAlgorithm $recommendationAlgorithm): bool
    {
        return $recommendationAlgorithm === RecommendationAlgorithm::MORE_THAN_ONE_WORD;
    }
}
