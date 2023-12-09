<?php

declare(strict_types=1);

namespace App\Core\Recommendation;

use App\Core\Enum\RecommendationAlgorithm;

final class WEvenLetterCountRecommendation implements MovieRecommendation
{
    public function recommend(RecommendationAlgorithm $recommendationAlgorithm, array $movies): array
    {
        $results = [];

        foreach ($movies as $movie) {
            $phrase = str_replace(' ', '', $movie);

            if (strtoupper($phrase)[0] !== 'W') {
                continue;
            }

            if (strlen($phrase) % 2) {
                continue;
            }

            $results[] = $movie;
        }

        return $results;
    }

    public function supports(RecommendationAlgorithm $recommendationAlgorithm): bool
    {
        return $recommendationAlgorithm === RecommendationAlgorithm::W_EVEN_LETTER_COUNT;
    }
}
