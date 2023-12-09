<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Repository;

use App\Core\Entity\Address;
use App\Core\Repository\AddressRepository;
use App\Tests\Utility\Factory\Core\AddressFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AddressRepositoryTest extends KernelTestCase
{
    protected AddressRepository $addressRepository;
    protected AddressFactory $addressFactory;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->addressRepository = $container->get(AddressRepository::class);
        $this->addressFactory = $container->get(AddressFactory::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testShouldSaveEntity(): void
    {
        $address = $this->addressFactory->anAddress(shouldPersist: false);

        $this->addressRepository->save($address, true);
        $this->entityManager->clear();

        $result = $this->addressRepository->find($address->getId()->jsonSerialize());
        $this->assertInstanceOf(Address::class, $result);
    }

    public function testShouldRemoveEntity(): void
    {
        $address = $this->addressFactory->anAddress();

        $this->addressRepository->remove($address, true);
        $this->entityManager->clear();

        $result = $this->addressRepository->find($address->getId()->jsonSerialize());
        $this->assertNull($result);
    }
}
