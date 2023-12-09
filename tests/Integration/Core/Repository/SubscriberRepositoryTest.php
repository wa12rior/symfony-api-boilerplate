<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Repository;

use App\Core\Entity\Subscriber\Subscriber;
use App\Core\Repository\Subscriber\SubscriberRepository;
use App\Tests\Utility\Factory\Core\SubscriberFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubscriberRepositoryTest extends KernelTestCase
{
    protected SubscriberRepository $subscriberRepository;
    protected SubscriberFactory $subscriberFactory;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->subscriberRepository = $container->get(SubscriberRepository::class);
        $this->subscriberFactory = $container->get(SubscriberFactory::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testShouldSaveEntity(): void
    {
        $subscriber = $this->subscriberFactory->aSubscriber(shouldPersist: false);

        $this->subscriberRepository->save($subscriber, true);
        $this->entityManager->clear();

        $result = $this->subscriberRepository->find($subscriber->getId()->jsonSerialize());
        $this->assertInstanceOf(Subscriber::class, $result);
    }

    public function testShouldRemoveEntity(): void
    {
        $subscriber = $this->subscriberFactory->aSubscriber();

        $this->subscriberRepository->remove($subscriber, true);
        $this->entityManager->clear();

        $result = $this->subscriberRepository->find($subscriber->getId()->jsonSerialize());
        $this->assertNull($result);
    }
}
