<?php

declare(strict_types=1);

namespace App\Tests\Functional\Core\Entity;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Core\Entity\Group;
use App\Core\Entity\Subscriber\Subscriber;
use App\Core\Repository\Subscriber\SubscriberRepository;
use App\Tests\Utility\ClientHelper;
use App\Tests\Utility\Factory\Core\GroupFactory;
use App\Tests\Utility\Factory\Core\SubscriberFactory;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriberTest extends ApiTestCase
{
    use ClientHelper;

    public const COLLECTION_IRI = '/api/subscribers';
    public const ENTITY_IRI = '/api/subscribers/%s';

    protected ?SubscriberFactory $subscriberFactory;
    protected ?GroupFactory $groupFactory;
    protected ?EntityManagerInterface $entityManager;
    protected ?SubscriberRepository $subscriberRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriberFactory = self::getContainer()->get(SubscriberFactory::class);
        $this->groupFactory = self::getContainer()->get(GroupFactory::class);
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->subscriberRepository = self::getContainer()->get(SubscriberRepository::class);
    }

    /**
     * @dataProvider URLs
     */
    public function testUnauthorized(string $url, string $method): void
    {
        $subscriber = $this->subscriberFactory->aSubscriber();

        static::createClient()->request($method, sprintf($url, $subscriber->getId()->jsonSerialize()));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function URLs(): array
    {
        return [
            [self::COLLECTION_IRI, Request::METHOD_GET],
            [self::ENTITY_IRI, Request::METHOD_GET],
        ];
    }

    public function testGetCollection(): void
    {
        $client = $this->getAuthenticatedClient();

        $subscriber = $this->subscriberFactory->aSubscriber(
            group: $this->groupFactory->aGroup(),
            email: 'sub@sub.sub'
        );

        // TODO: check why it is not visible in api
//        $this->entityManager->clear();
//        $subscriber = $this->subscriberRepository->find($subscriber->getId()->jsonSerialize());
//        dd($subscriber->getGroups()->toArray());
        /** @phpstan-ignore-next-line */
        $groups = array_map(fn(Group $group) => sprintf(GroupTest::ENTITY_IRI, $group->getId()->jsonSerialize()), $subscriber->getGroups()->toArray());

        $response = $client->request('GET', self::COLLECTION_IRI);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

//        $this->assertCount(1, $groups);
        $this->assertJsonContains([
            '@context' => '/api/contexts/Subscriber',
            '@id' => '/api/subscribers',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 1,
            'hydra:member' => [
                0 => [
                    '@id' => sprintf(self::ENTITY_IRI, $subscriber->getId()->jsonSerialize()),
                    '@type' => 'Subscriber',
                    'id' => $subscriber->getId()->jsonSerialize(),
                    'groups' => $groups,
                    'email' => $subscriber->getEmail(),
                    'invitedBy' => $subscriber->getInvitedBy(),
                    'createdAt' => $subscriber->getCreatedAt()->format(DateTime::ATOM),
                    'updatedAt' => $subscriber->getUpdatedAt()->format(DateTime::ATOM),
                ]
            ],
        ]);

        $this->assertCount(1, $response->toArray()['hydra:member']);
        $this->assertMatchesResourceCollectionJsonSchema(Subscriber::class);
    }
}
