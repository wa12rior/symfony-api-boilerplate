<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Processor;

use ApiPlatform\Metadata\Operation;
use App\Core\Entity\Subscriber\Subscriber;
use App\Core\Entity\Subscriber\SubscriberGroup;
use App\Core\Processor\SubscriberAssignToGroupProcessor;
use App\Core\Repository\GroupRepository;
use App\Core\Repository\Subscriber\SubscriberGroupRepository;
use App\Core\Repository\Subscriber\SubscriberRepository;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SubscriberAssignToGroupProcessorTest extends TestCase
{
    public function testShouldThrowConflictHttpExceptionWhenDataIsNotSubscriberClass(): void
    {
        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Resource should be Subscriber');

        $processor = new SubscriberAssignToGroupProcessor(
            $this->createMock(SubscriberGroupRepository::class),
            $this->createMock(GroupRepository::class),
            $this->createMock(SubscriberRepository::class),
        );

        $processor->process(new stdClass(), $this->createMock(Operation::class), ['groupId' => '123'], []);
    }

    public function testShouldThrowConflictHttpExceptionWhenGroupIdIsNotPresent(): void
    {
        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Group ID for subscriber is not present');

        $processor = new SubscriberAssignToGroupProcessor(
            $this->createMock(SubscriberGroupRepository::class),
            $this->createMock(GroupRepository::class),
            $this->createMock(SubscriberRepository::class),
        );

        $processor->process(new Subscriber(), $this->createMock(Operation::class), [], []);
    }

    public function testShouldThrowUnprocessableEntityHttpException(): void
    {
        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Subscriber already assigned to this group');

        $subscriber = new Subscriber();
        $subscriber->setEmail('123');

        $subscriberRepository = $this->createMock(SubscriberRepository::class);
        $subscriberRepository->method('findOneBy')->willReturn($subscriber);

        $subscriberGroupRepository = $this->createMock(SubscriberGroupRepository::class);
        $subscriberGroupRepository->method('findOneBy')->willReturn(new SubscriberGroup());

        $processor = new SubscriberAssignToGroupProcessor(
            $subscriberGroupRepository,
            $this->createMock(GroupRepository::class),
            $subscriberRepository,
        );

        $processor->process($subscriber, $this->createMock(Operation::class), ['groupId' => '123'], []);
    }
}
