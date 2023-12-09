<?php

declare(strict_types=1);

namespace App\Tests\Functional\Core\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Core\Entity\Group;
use App\Core\Entity\Subscriber\Subscriber;
use App\Core\Enum\GroupType;
use App\Core\Repository\GroupRepository;
use App\Core\Repository\Subscriber\SubscriberGroupRepository;
use App\Core\Repository\Subscriber\SubscriberRepository;
use App\Tests\Utility\ClientHelper;
use App\Tests\Utility\Factory\Core\GroupFactory;
use App\Tests\Utility\Factory\Core\SubscriberFactory;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GroupTest extends ApiTestCase
{
    use ClientHelper;

    public const SUBRESOURCE_COLLECTION_IRI = '/api/groups/%s/subscribers';
    public const COLLECTION_IRI = '/api/groups';
    public const SUBRESOURCE_ENTITY_IRI = '/api/groups/%s/subscribers/%s';
    public const ENTITY_IRI = '/api/groups/%s';

    protected ?GroupFactory $groupFactory;
    protected ?SubscriberRepository $subscriberRepository;
    protected ?SubscriberGroupRepository $subscriberGroupRepository;
    protected ?SubscriberFactory $subscriberFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->groupFactory = self::getContainer()->get(GroupFactory::class);
        $this->subscriberRepository = self::getContainer()->get(SubscriberRepository::class);
        $this->subscriberGroupRepository = self::getContainer()->get(SubscriberGroupRepository::class);
        $this->subscriberFactory = self::getContainer()->get(SubscriberFactory::class);
    }

    /**
     * @dataProvider URLs
     */
    public function testUnauthorized(string $url, string $method): void
    {
        $group = $this->groupFactory->aGroup(
            GroupType::OPEN,
            'Zarząd spółki x',
        );

        static::createClient()->request($method, sprintf($url, $group->getId()->jsonSerialize()), ['json' => [
            'groupType' => 'OPEN',
            'name' => 'Zarz',
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
            [self::ENTITY_IRI, Request::METHOD_DELETE],
        ];
    }

    public function testGetCollection(): void
    {
        $client = $this->getAuthenticatedClient();

        $group = $this->groupFactory->aGroup(
            GroupType::OPEN,
            'zarząd spółki x',
        );

        $response = $client->request('GET', self::COLLECTION_IRI);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $this->assertJsonContains([
            '@context' => '/api/contexts/Group',
            '@id' => '/api/groups',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                0 => [
                    '@id' => sprintf(self::ENTITY_IRI, $group->getId()->jsonSerialize()),
                    '@type' => 'Group',
                    'id' => $group->getId()->jsonSerialize(),
                    'type' => $group->getType()->value,
                    'name' => $group->getName(),
                    'subscribers' => [],
                    'workflows' => [],
                    'createdBy' => $group->getCreatedBy(),
                    'createdAt' => $group->getCreatedAt()->format(DateTime::ATOM),
                    'updatedAt' => $group->getUpdatedAt()->format(DateTime::ATOM),
                ]
            ],
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Group::class);
    }

    public function testCreateGroup(): void
    {
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'type' => 'OPEN',
            'name' => 'Zarzad spolki x',
        ]]);
        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/Group',
            '@type' => 'Group',
            'type' => 'OPEN',
            'name' => 'Zarzad spolki x',
        ]);
        $this->assertMatchesResourceItemJsonSchema(Group::class);
    }

    public function testCreateInvalidGroup(): void
    {
        $client = $this->getAuthenticatedClient();
        $client->request('POST', self::COLLECTION_IRI, ['json' => [
            'type' => 'OPEN',
            'name' => '',
        ]]);
        $this->assertResponseStatusCodeSame(422);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertJsonContains([
            '@context' => '/api/contexts/ConstraintViolationList',
            '@type' => 'ConstraintViolationList',
            'hydra:title' => 'An error occurred',
            'hydra:description' => 'name: This value is too short. It should have 2 characters or more.',
        ]);
    }

    public function testUpdateGroup(): void
    {
        $group = $this->groupFactory->aGroup(
            GroupType::OPEN,
            'zarząd spółki x',
        );

        $client = $this->getAuthenticatedClient();
        // Use the PATCH method here to do a partial update
        $iri = sprintf(self::ENTITY_IRI, $group->getId()->jsonSerialize());
        $client->request('PATCH', $iri, [
            'json' => [
                'name' => 'zarzad',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ]
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => $iri,
            'id' => $group->getId()->jsonSerialize(),
            'name' => 'zarzad',
        ]);
    }

    public function testDeleteGroup(): void
    {
        // Only create the book we need with a given ISBN
        $group = $this->groupFactory->aGroup(
            GroupType::OPEN,
            'zarząd spółki x',
        );

        $client = $this->getAuthenticatedClient();
        $iri = sprintf(self::ENTITY_IRI, $group->getId()->jsonSerialize());

        $client->request('DELETE', $iri);
        $this->assertResponseStatusCodeSame(204);
        $this->assertNull(
            static::getContainer()->get(GroupRepository::class)->find(['id' => $group->getId()->jsonSerialize()])
        );
    }

    public function testAssignSubscriberToGroup(): void
    {
        $group = $this->groupFactory->aGroup(
            GroupType::OPEN,
            'zarząd spółki x',
        );

        $client = $this->getAuthenticatedClient();
        $client->request(
            'POST',
            sprintf(self::SUBRESOURCE_COLLECTION_IRI, $group->getId()->jsonSerialize()),
            [
                'json' => [
                    'email' => 'localhost@localhost.localhost',
                ]
            ]
        );

        $subscriberGroup = $this->subscriberGroupRepository->findOneBy(['group' => $group->getId()->jsonSerialize()]);
        $subscriber = $subscriberGroup->getSubscriber();

        $this->assertResponseStatusCodeSame(201);
        $this->assertInstanceOf(Subscriber::class, $subscriber);
        $this->assertSame('localhost@localhost.localhost', $subscriber->getEmail());
    }

    public function testUnassignSubscriberFromGroup(): void
    {
        $group = $this->groupFactory->aGroup(
            GroupType::OPEN,
            'zarząd spółki x',
        );

        $subscriber = $this->subscriberFactory->aSubscriber($group);

        $this->assertNotNull(
            static::getContainer()->get(SubscriberRepository::class)->find(['id' => $subscriber->getId()->jsonSerialize()])
        );

        $client = $this->getAuthenticatedClient();
        $client->request(
            'DELETE',
            sprintf(
                self::SUBRESOURCE_ENTITY_IRI,
                $group->getId()->jsonSerialize(),
                $subscriber->getId()->jsonSerialize(),
            ),
        );

        $subscriberGroup = $this->subscriberGroupRepository->findOneBy(['group' => $group->getId()->jsonSerialize()]);

        $this->assertResponseStatusCodeSame(204);
        $this->assertNull($subscriberGroup);
    }
}
