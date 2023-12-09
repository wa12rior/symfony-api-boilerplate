<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Recommendation;

use App\Core\Enum\RecommendationAlgorithm;
use App\Core\Recommendation\MoreThanOneWordRecommendation;
use App\Core\Recommendation\MovieRecommendation;
use App\Core\Recommendation\WEvenLetterCountRecommendation;
use PHPUnit\Framework\TestCase;

class MoreThanOneWordRecommendationTest extends TestCase
{
    protected MovieRecommendation $movieRecommendation;

    protected function setUp(): void
    {
        $this->movieRecommendation = new MoreThanOneWordRecommendation();
    }

    public function testShouldSupportWEvenLetterCountEnum(): void
    {
        $result = $this->movieRecommendation->supports(RecommendationAlgorithm::MORE_THAN_ONE_WORD);

        self::assertTrue($result);
    }

    /**
     * @param string[] $expected
     * @param string[] $given
     * @dataProvider moviesData
     */
    public function testShouldRecommendMovies(array $given, array $expected): void
    {
        $result = $this->movieRecommendation->recommend(RecommendationAlgorithm::MORE_THAN_ONE_WORD, $given);

        self::assertEquals($expected, $result);
    }

    /**
     * @return mixed[]
     */
    public function moviesData(): array
    {
        return [
            'it should return empty array when one word' => [
                [
                    "Wulp",
                ],
                [
                ]
            ],
            'it should return empty array when one word and trim' => [
                [
                    "  Wul   ",
                ],
                []
            ],
            'it should return array with one movie when more than one word' => [
                [
                    "Xul Xul",
                ],
                [
                    "Xul Xul",
                ]
            ],
            'it should return array with one movie when more than one word and trim' => [
                [
                    "   Xul Xul ",
                ],
                [
                    "   Xul Xul ",
                ]
            ]
        ];
    }
}
