<?php

declare(strict_types=1);

namespace App\Tests\Functional\Core\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Core\Entity\Address;
use App\Core\Enum\AddressType;
use App\Tests\Utility\ClientHelper;
use App\Tests\Utility\Factory\Core\AddressFactory;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AddressTest extends ApiTestCase
{
    use ClientHelper;

    public const COLLECTION_IRI = '/api/addresses';
    public const ENTITY_IRI = '/api/addresses/%s';

    protected ?AddressFactory $addressFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addressFactory = self::getContainer()->get(AddressFactory::class);
    }

    /**
     * @dataProvider URLs
     */
    public function testUnauthorized(string $url, string $method): void
    {
        $address = $this->addressFactory->anAddress(
            AddressType::COMPANY,
            'apartment 2',
            'country 2',
            'town 2',
            'street 2',
            'phoneNumber 2',
            'postalCode 2',
        );

        static::createClient()->request($method, sprintf($url, $address->getId()->jsonSerialize()), ['json' => [
            'addressType' => 'DEFAULT',
            'phoneNumber' => '123123123',
            'street' => 'ul. Grove 15',
            'apartment' => 'm.42',
            'postalCode' => '33-333',
            'town' => 'Warszawa',
            'country' => 'PL',
        ]]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function URLs(): array
    {
        return [
            [self::COLLECTION_IRI, Request::METHOD_GET],
            [self::COLLECTION_IRI, Request::METHOD_POST],
            [self::ENTITY_IRI, Request::METHOD_GET],
            [self::ENTITY_IRI, Request::METHOD_PUT],
            [self::ENTITY_IRI, Request::METHOD_PATCH],
        ];
    }

    public function testGetCollection(): void
    {
        $client = $this->getAuthenticatedClient();

        $address = $this->addressFactory->anAddress(
            AddressType::COMPANY,
            'm.42',
            'PL',
            'Warszawa',
            'ul. Grove 15',
            '123123123',
            '33-333',
        );

        $response = $client->request('GET', self::COLLECTION_IRI);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Address',
            '@id' => '/api/addresses',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                0 => [
                    '@id' => sprintf(self::ENTITY_IRI, $address->getId()->jsonSerialize()),
                    '@type' => 'Address',
                    'id' => $address->getId()->jsonSerialize(),
                    'addressType' => $address->getAddressType()->value,
                    'phoneNumber' => $address->getPhoneNumber(),
                    'street' => $address->getStreet(),
                    'apartment' => $address->getApartment(),
                    'postalCode' => $address->getPostalCode(),
                    'town' => $address->getTown(),
                    'country' => $address->getCountry(),
                    'createdBy' => $address->getCreatedBy(),
                    'createdAt' => $address->getCreatedAt()->format(DateTime::ATOM),
                    'updatedAt' => $address->getUpdatedAt()->format(DateTime::ATOM),
                ]
            ],
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Address::class);
    }

    public function testCreateAddress(): void
    {
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'addressType' => 'DEFAULT',
            'phoneNumber' => '123123123',
            'street' => 'ul. Grove 15',
            'apartment' => 'm.42',
            'postalCode' => '33-333',
            'town' => 'Warszawa',
            'country' => 'PL',
        ]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Address',
            '@type' => 'Address',
            'addressType' => 'DEFAULT',
            'phoneNumber' => '123123123',
            'street' => 'ul. Grove 15',
            'apartment' => 'm.42',
            'postalCode' => '33-333',
            'town' => 'Warszawa',
            'country' => 'PL',
        ]);
        $this->assertMatchesResourceItemJsonSchema(Address::class);
    }

    public function testCreateInvalidAddress(): void
    {
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'addressType' => 'DEFAULT',
            'phoneNumber' => '123123123',
            'street' => 'ul. Grove 15',
            'apartment' => 'm.42',
            'postalCode' => '33-333',
            'town' => 'Warszawa',
            'country' => 'xyz',
        ]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'country: This value is not a valid country.',
        ]);
    }

    public function testUpdateAddress(): void
    {
        $address = $this->addressFactory->anAddress(
            AddressType::COMPANY,
            'm.42',
            'PL',
            'Warszawa',
            'ul. Grove 15',
            '123123123',
            '33-333',
        );

        $client = $this->getAuthenticatedClient();
        // Use the PATCH method here to do a partial update
        $iri = sprintf(self::ENTITY_IRI, $address->getId()->jsonSerialize());
        $client->request('PATCH', $iri, [
            'json' => [
                'town' => 'Kraków',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'id' => $address->getId()->jsonSerialize(),
            'town' => 'Kraków',
        ]);
    }
}
