<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Repository;

use App\Core\Entity\Workflow\Workflow;
use App\Core\Repository\Workflow\WorkflowRepository;
use App\Tests\Utility\Factory\Core\WorkflowFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WorkflowRepositoryTest extends KernelTestCase
{
    protected WorkflowRepository $workflowRepository;
    protected WorkflowFactory $workflowFactory;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->workflowRepository = $container->get(WorkflowRepository::class);
        $this->workflowFactory = $container->get(WorkflowFactory::class);
        $this->entityManager = $container->get(EntityManagerInterface::class);
    }

    public function testShouldSaveEntity(): void
    {
        $workflow = $this->workflowFactory->aWorkflow(shouldPersist: false);

        $this->workflowRepository->save($workflow, true);
        $this->entityManager->clear();

        $result = $this->workflowRepository->find($workflow->getId()->jsonSerialize());
        $this->assertInstanceOf(Workflow::class, $result);
    }

    public function testShouldRemoveEntity(): void
    {
        $workflow = $this->workflowFactory->aWorkflow();

        $this->workflowRepository->remove($workflow, true);
        $this->entityManager->clear();

        $result = $this->workflowRepository->find($workflow->getId()->jsonSerialize());
        $this->assertNull($result);
    }
}
