<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Repository;

use App\Core\Entity\Company;
use App\Core\Repository\CompanyRepository;
use App\Tests\Utility\Factory\Core\CompanyFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CompanyRepositoryTest extends KernelTestCase
{
    protected CompanyRepository $companyRepository;
    protected CompanyFactory $companyFactory;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->companyRepository = $container->get(CompanyRepository::class);
        $this->companyFactory = $container->get(CompanyFactory::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testShouldSaveEntity(): void
    {
        $company = $this->companyFactory->aCompany(shouldPersist: false);

        $this->companyRepository->save($company, true);
        $this->entityManager->clear();

        $result = $this->companyRepository->find($company->getId()->jsonSerialize());
        $this->assertInstanceOf(Company::class, $result);
    }

    public function testShouldRemoveEntity(): void
    {
        $company = $this->companyFactory->aCompany();

        $this->companyRepository->remove($company, true);
        $this->entityManager->clear();

        $result = $this->companyRepository->find($company->getId()->jsonSerialize());
        $this->assertNull($result);
    }
}
