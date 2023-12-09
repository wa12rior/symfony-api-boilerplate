<?php

declare(strict_types=1);

namespace App\Tests\Integration\Core\Notification;

use App\Core\Notification\DeadlineNotificationSender;
use App\Tests\Utility\Factory\Core\WorkflowFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\Transport\TransportInterface;

class DeadlineNotificationSenderTest extends KernelTestCase
{
    private ?DeadlineNotificationSender $notificationSender;
    private ?TransportInterface $transport;
    private ?WorkflowFactory $workflowFactory;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->transport = $container->get('messenger.transport.sync');

        $this->notificationSender = $container->get(DeadlineNotificationSender::class);
        $this->workflowFactory = $container->get(WorkflowFactory::class);
    }

    public function testShouldSendCommandOnce(): void
    {
        $this->workflowFactory->aWorkflow(notifyBeforeHours: 24, deadline: new \DateTime('+1 hour'));
        $this->workflowFactory->aWorkflow(notifyBeforeHours: 24, deadline: new \DateTime('+3 day'));
        $this->workflowFactory->aWorkflow(notifyBeforeHours: 24, deadline: new \DateTime('+1 hour'), notifiedAt: new \DateTime('-30 minutes'));

        $this->notificationSender->send();

        $this->assertCount(1, $this->transport->get());
    }
}
