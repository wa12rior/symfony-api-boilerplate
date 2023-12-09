<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Repository;

use App\Core\Entity\Group;
use App\Core\Repository\GroupRepository;
use App\Tests\Utility\Factory\Core\GroupFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GroupRepositoryTest extends KernelTestCase
{
    protected GroupRepository $groupRepository;
    protected GroupFactory $groupFactory;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->groupRepository = $container->get(GroupRepository::class);
        $this->groupFactory = $container->get(GroupFactory::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testShouldSaveEntity(): void
    {
        $group = $this->groupFactory->aGroup(shouldPersist: false);

        $this->groupRepository->save($group, true);
        $this->entityManager->clear();

        $result = $this->groupRepository->find($group->getId()->jsonSerialize());
        $this->assertInstanceOf(Group::class, $result);
    }

    public function testShouldRemoveEntity(): void
    {
        $group = $this->groupFactory->aGroup();

        $this->groupRepository->remove($group, true);
        $this->entityManager->clear();

        $result = $this->groupRepository->find($group->getId()->jsonSerialize());
        $this->assertNull($result);
    }
}
