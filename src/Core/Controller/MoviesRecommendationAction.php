<?php

declare(strict_types=1);

namespace App\Core\Controller;

use App\Core\DTO\Request\RecommendationRequest;
use App\Core\Enum\Movies;
use App\Core\Recommendation\MovieRecommendation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Traversable;

#[AsController]
class MoviesRecommendationAction extends AbstractController
{
    /**
     * @param Traversable<int, MovieRecommendation> $recommendationStrategies
     */
    public function __construct(
        private Traversable $recommendationStrategies,
    ) {
    }

    /**
     * @return string[]|JsonResponse
     */
    public function __invoke(Request $request, #[MapQueryString] RecommendationRequest $data): array|JsonResponse
    {
        $movies = Movies::fetchAll();

        foreach ($this->recommendationStrategies as $recommendationStrategy) {
            if ($recommendationStrategy->supports($data->recommendationAlgorithm)) {
                return $this->json($recommendationStrategy->recommend($data->recommendationAlgorithm, $movies));
            }
        }

        return $this->json(['message' => 'Strategy not found'], JsonResponse::HTTP_CONFLICT);
    }
}
