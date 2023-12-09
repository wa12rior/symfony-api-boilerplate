<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Repository;

use App\Core\Entity\Document\Document;
use App\Core\Repository\Document\DocumentRepository;
use App\Tests\Utility\Factory\Core\DocumentFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentRepositoryTest extends KernelTestCase
{
    protected DocumentRepository $eventRepository;
    protected DocumentFactory $documentFactory;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->eventRepository = $container->get(DocumentRepository::class);
        $this->documentFactory = $container->get(DocumentFactory::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testShouldSaveEntity(): void
    {
        $this->markTestSkipped('document api changed, need more work');
        /** @phpstan-ignore-next-line */
        $event = $this->documentFactory->aDocument(shouldPersist: false);

        $this->eventRepository->save($event, true);
        $this->entityManager->clear();

        $result = $this->eventRepository->find($event->getId()->jsonSerialize());
        $this->assertInstanceOf(Document::class, $result);
    }

    public function testShouldRemoveEntity(): void
    {
        $this->markTestSkipped('document api changed, need more work');
        /** @phpstan-ignore-next-line */
        $event = $this->documentFactory->aDocument();

        $this->eventRepository->remove($event, true);
        $this->entityManager->clear();

        $result = $this->eventRepository->find($event->getId()->jsonSerialize());
        $this->assertNull($result);
    }
}
