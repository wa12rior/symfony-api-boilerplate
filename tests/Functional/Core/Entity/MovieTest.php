<?php

declare(strict_types=1);

namespace App\Tests\Functional\Core\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Core\Entity\Address;
use App\Core\Entity\Movie;
use App\Core\Enum\AddressType;
use App\Tests\Utility\ClientHelper;
use App\Tests\Utility\Factory\Core\AddressFactory;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MovieTest extends ApiTestCase
{
    use ClientHelper;

    public const COLLECTION_IRI = '/api/movies';

    protected function setUp(): void
    {
        parent::setUp();
    }

//    /**
//     * @dataProvider URLs
//     */
//    public function testUnauthorized(string $url, string $method): void
//    {
//        static::createClient()->request($method, sprintf($url), ['json' => [
//            'addressType' => 'DEFAULT',
//            'phoneNumber' => '123123123',
//            'street' => 'ul. Grove 15',
//            'apartment' => 'm.42',
//            'postalCode' => '33-333',
//            'town' => 'Warszawa',
//            'country' => 'PL',
//        ]]);
//
//        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
//    }
//
//    /**
//     * @return array<int, array<int, string>>
//     */
//    public function URLs(): array
//    {
//        return [
//            [self::COLLECTION_IRI, Request::METHOD_GET],
//        ];
//    }

    public function testGetCollection(): void
    {
        $client = $this->getAuthenticatedClient();

        $response = $client->request('GET', self::COLLECTION_IRI, ['query' => ['recommendationAlgorithm' => 'RANDOM_THREE']]);

        $jsonResponse = json_decode($response->getContent(false), true);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $this->assertIsArray($jsonResponse);
    }
}
