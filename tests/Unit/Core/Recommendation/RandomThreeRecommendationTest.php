<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Recommendation;

use App\Core\Enum\Movies;
use App\Core\Enum\RecommendationAlgorithm;
use App\Core\Recommendation\MovieRecommendation;
use App\Core\Recommendation\RandomThreeRecommendation;
use PHPUnit\Framework\TestCase;

class RandomThreeRecommendationTest extends TestCase
{
    protected MovieRecommendation $movieRecommendation;

    protected function setUp(): void
    {
        $this->movieRecommendation = new RandomThreeRecommendation();
    }

    public function testShouldSupportWEvenLetterCountEnum(): void
    {
        $result = $this->movieRecommendation->supports(RecommendationAlgorithm::RANDOM_THREE);

        self::assertTrue($result);
    }

    public function testShouldReturnRandomMoviesWhileMoreThanThreeInArray(): void
    {
        $movies = Movies::fetchAll();
        $result = $this->movieRecommendation->recommend(RecommendationAlgorithm::RANDOM_THREE, $movies);

        self::assertCount(3, $result);
    }

    public function testShouldReturnSameMoviesWhileLessOrEqualThanThreeInArray(): void
    {
        $movies = ['test', 'test2'];
        $result = $this->movieRecommendation->recommend(RecommendationAlgorithm::RANDOM_THREE, $movies);

        self::assertCount(2, $result);
        self::assertSame($result, $movies);
    }
}
