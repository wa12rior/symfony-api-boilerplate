<?php

declare(strict_types=1);

namespace App\Core\Recommendation;

use App\Core\Enum\RecommendationAlgorithm;

final class RandomThreeRecommendation implements MovieRecommendation
{
    public function recommend(RecommendationAlgorithm $recommendationAlgorithm, array $movies): array
    {
        if (count($movies) <= 3) {
            return $movies;
        };

        $randomIndexes = array_rand($movies, 3);

        return array_map(fn (int $index) => $movies[$index], $randomIndexes);
    }

    public function supports(RecommendationAlgorithm $recommendationAlgorithm): bool
    {
        return $recommendationAlgorithm === RecommendationAlgorithm::RANDOM_THREE;
    }
}
