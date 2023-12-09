<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Repository;

use App\Core\Entity\Workflow\Folder;
use App\Core\Repository\Workflow\FolderRepository;
use App\Tests\Utility\Factory\Core\FolderFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FolderRepositoryTest extends KernelTestCase
{
    protected FolderRepository $folderRepository;
    protected FolderFactory $folderFactory;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->folderRepository = $container->get(FolderRepository::class);
        $this->folderFactory = $container->get(FolderFactory::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testShouldSaveEntity(): void
    {
        $folder = $this->folderFactory->aFolder(shouldPersist: false);

        $this->folderRepository->save($folder, true);
        $this->entityManager->clear();

        $result = $this->folderRepository->find($folder->getId()->jsonSerialize());
        $this->assertInstanceOf(Folder::class, $result);
    }

    public function testShouldRemoveEntity(): void
    {
        $folder = $this->folderFactory->aFolder();

        $this->folderRepository->remove($folder, true);
        $this->entityManager->clear();

        $result = $this->folderRepository->find($folder->getId()->jsonSerialize());
        $this->assertNull($result);
    }
}
