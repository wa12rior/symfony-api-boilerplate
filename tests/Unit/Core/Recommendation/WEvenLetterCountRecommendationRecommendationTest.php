<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Recommendation;

use App\Core\Enum\RecommendationAlgorithm;
use App\Core\Recommendation\MovieRecommendation;
use App\Core\Recommendation\WEvenLetterCountRecommendation;
use PHPUnit\Framework\TestCase;

class WEvenLetterCountRecommendationRecommendationTest extends TestCase
{
    protected MovieRecommendation $movieRecommendation;

    protected function setUp(): void
    {
        $this->movieRecommendation = new WEvenLetterCountRecommendation();
    }

    public function testShouldSupportWEvenLetterCountEnum(): void
    {
        $result = $this->movieRecommendation->supports(RecommendationAlgorithm::W_EVEN_LETTER_COUNT);

        self::assertTrue($result);
    }

    /**
     * @dataProvider moviesData
     */
    public function testShouldRecommendMovies(array $given, array $expected): void
    {
        $result = $this->movieRecommendation->recommend(RecommendationAlgorithm::W_EVEN_LETTER_COUNT, $given);

        self::assertEquals($expected, $result);
    }

    public function moviesData(): array
    {
        return [
            'it should trim right side and return with even letter count and first w' => [
                [
                    "Wulp  ",
                ],
                [
                    "Wulp  ",
                ]
            ],
            'it should trim left side and return empty array with odd letter count and first w' => [
                [
                    "  Wul",
                ],
                []
            ],
            'it should trim left&right side and return empty array with odd letter count and first X' => [
                [
                    "  Xul  ",
                ],
                []
            ]
        ];
    }
}
