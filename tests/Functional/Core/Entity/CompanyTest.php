<?php

declare(strict_types=1);

namespace App\Tests\Functional\Core\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Core\Entity\Company;
use App\Tests\Utility\ClientHelper;
use App\Tests\Utility\Factory\Core\CompanyFactory;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CompanyTest extends ApiTestCase
{
    use ClientHelper;

    public const COLLECTION_IRI = '/api/companies';
    public const ENTITY_IRI = '/api/companies/%s';

    protected ?CompanyFactory $companyFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyFactory = self::getContainer()->get(CompanyFactory::class);
    }

    /**
     * @dataProvider URLs
     */
    public function testUnauthorized(string $url, string $method): void
    {
        $company = $this->companyFactory->aCompany(
            'company@localhost.com',
            'Company name',
            '12948012',
        );

        static::createClient()->request($method, sprintf($url, $company->getId()->jsonSerialize()), ['json' => [
            'email' => 'company@localhost',
            'companyName' => 'Company name',
            'companyNumber' => '12948012',
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

        $company = $this->companyFactory->aCompany(
            'company@localhost.com',
            'Company name',
            '12948012',
        );

        $response = $client->request('GET', self::COLLECTION_IRI);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Company',
            '@id' => '/api/companies',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                0 => [
                    '@id' => sprintf(self::ENTITY_IRI, $company->getId()->jsonSerialize()),
                    '@type' => 'Company',
                    'id' => $company->getId()->jsonSerialize(),
                    'email' => $company->getEmail(),
                    'companyName' => $company->getCompanyName(),
                    'companyNumber' => $company->getCompanyNumber(),
                    'createdBy' => $company->getCreatedBy(),
                    'createdAt' => $company->getCreatedAt()->format(DateTime::ATOM),
                    'updatedAt' => $company->getUpdatedAt()->format(DateTime::ATOM),
                ]
            ],
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Company::class);
    }

    public function testCreateCompany(): void
    {
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'email' => 'company@localhost.com',
            'companyName' => 'Company name',
            'companyNumber' => '12948012',
        ]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Company',
            '@type' => 'Company',
            'email' => 'company@localhost.com',
            'companyName' => 'Company name',
            'companyNumber' => '12948012',
        ]);
        $this->assertMatchesResourceItemJsonSchema(Company::class);
    }

    public function testCreateInvalidCompany(): void
    {
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'email' => 'companylocalhost.com',
            'companyName' => 'Company name',
            'companyNumber' => '12948012',
        ]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'email: This value is not a valid email address.',
        ]);
    }

    public function testUpdateCompany(): void
    {
        $company = $this->companyFactory->aCompany(
            'company@localhost.com',
            'Company name',
            '12948012',
        );

        $client = $this->getAuthenticatedClient();
        // Use the PATCH method here to do a partial update
        $iri = sprintf(self::ENTITY_IRI, $company->getId()->jsonSerialize());
        $client->request('PATCH', $iri, [
            'json' => [
                'companyName' => 'xxx',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'id' => $company->getId()->jsonSerialize(),
            'companyName' => 'xxx',
        ]);
    }
}
