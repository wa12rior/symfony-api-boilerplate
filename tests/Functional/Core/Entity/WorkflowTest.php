<?php

declare(strict_types=1);

namespace App\Tests\Functional\Core\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Core\Entity\Workflow\Workflow;
use App\Tests\Utility\ClientHelper;
use App\Tests\Utility\Factory\Core\WorkflowFactory;
use DateTime;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkflowTest extends ApiTestCase
{
    use ClientHelper;

    public const COLLECTION_IRI = '/api/workflows';
    public const ENTITY_IRI = '/api/workflows/%s';

    public const ENTITY_PREVIEW_IRI = '/api/workflows/preview';

    protected ?WorkflowFactory $workflowFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workflowFactory = self::getContainer()->get(WorkflowFactory::class);
    }

    /**
     * @dataProvider URLs
     */
    public function testUnauthorized(string $url, string $method): void
    {
        $workflow = $this->workflowFactory->aWorkflow(
            'title',
            'description',
            'shortdescription',
        );

        static::createClient()->request($method, sprintf($url, $workflow->getId()->jsonSerialize()), ['json' => [
            'email' => 'company@localhost',
            'companyName' => 'Workflow name',
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

        $deadline = new DateTime();

        $workflow = $this->workflowFactory->aWorkflow(
            'title ',
            'Workflow name',
            '12948012',
            deadline: $deadline,
        );

        $response = $client->request('GET', self::COLLECTION_IRI);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Workflow',
            '@id' => '/api/workflows',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                0 => [
                    '@id' => sprintf(self::ENTITY_PREVIEW_IRI),
                    '@type' => 'Workflow',
                    'id' => $workflow->getId()->jsonSerialize(),
                    'title' => 'title ',
                    'shortDescription' => '12948012',
                    'deadline' => $deadline->format(DateTimeInterface::ATOM),
                    'notifyBeforeHours' => 12,
                    'folder' => null,
                    'group' => null,
                    'description' => 'Workflow name',
                    'todos' => [],
                    'events' => [],
                    'attachments' => [],
                    'createdBy' => $workflow->getCreatedBy(),
                    'createdAt' => $workflow->getCreatedAt()->format(DateTime::ATOM),
                    'updatedAt' => $workflow->getUpdatedAt()->format(DateTime::ATOM),
                ]
            ],
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Workflow::class);
    }

    public function testCreateWorkflow(): void
    {
        $deadline = new DateTime();
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'title' => 'title',
            'shortDescription' => 'short description',
            'deadline' => $deadline->format(DateTimeInterface::ATOM),
            'notifyBeforeHours' => 12
        ]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Workflow',
            '@type' => 'Workflow',
            'title' => 'title',
            'shortDescription' => 'short description',
            'notifyBeforeHours' => 12,
            'deadline' => $deadline->format(DateTimeInterface::ATOM),
        ]);
        $this->assertMatchesResourceItemJsonSchema(Workflow::class);
    }

    public function testCreateWorkflowWithTodos(): void
    {
        $deadline = new DateTime();
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'title' => 'title',
            'shortDescription' => 'short description',
            'deadline' => $deadline->format(DateTimeInterface::ATOM),
            'notifyBeforeHours' => 12,
            'todos' => [
                [
                    'content' => 'Test todo 1',
                ],
                [
                    'content' => 'Test todo 2',
                ],
            ]
        ]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Workflow',
            '@type' => 'Workflow',
            'title' => 'title',
            'shortDescription' => 'short description',
            'notifyBeforeHours' => 12,
            'deadline' => $deadline->format(DateTimeInterface::ATOM),
            'todos' => [
                [
                    '@type' => 'Todo',
                    'content' => 'Test todo 1',
                ],
                [
                    '@type' => 'Todo',
                    'content' => 'Test todo 2',
                ],
            ]
        ]);
        $this->assertMatchesResourceItemJsonSchema(Workflow::class);
    }

    public function testCreateInvalidWorkflow(): void
    {
        $deadline = new DateTime();
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'title' => 'title',
            'shortDescription' => 'short description',
            'deadline' => $deadline->format(DateTimeInterface::ATOM),
        ]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'notifyBeforeHours: This value should not be null.',
        ]);
    }

    public function testUpdateWorkflow(): void
    {
        $workflow = $this->workflowFactory->aWorkflow(
            'company@localhost.com',
            'Workflow name',
            '12948012',
        );

        $client = $this->getAuthenticatedClient();
        // Use the PATCH method here to do a partial update
        $iri = sprintf(self::ENTITY_IRI, $workflow->getId()->jsonSerialize());
        $response = $client->request('PUT', $iri, [
            'json' => [
                'title' => 'xxx',
                'shortDescription' => 'desc',
                'deadline' => (new DateTime())->format(DateTime::ATOM),
                'todos' => [
                    [
                        'content' => 'todo 1'
                    ],
                    [
                        'content' => 'todo 2'
                    ],
                ]
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'id' => $workflow->getId()->jsonSerialize(),
            'title' => 'xxx',
            'todos' => [
                [
                    'content' => 'todo 1'
                ],
                [
                    'content' => 'todo 2'
                ],
            ]
        ]);
    }
}
