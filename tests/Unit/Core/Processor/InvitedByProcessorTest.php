<?php

declare(strict_types=1);

namespace App\Tests\Unit\Core\Processor;

use ApiPlatform\Metadata\Operation;
use App\Core\Processor\InvitedByProcessor;
use App\Core\Repository\GroupRepository;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

class InvitedByProcessorTest extends TestCase
{
    public function testShouldThrowConflictHttpExceptionWhenDataIsNotGroupClass(): void
    {
        $this->expectException(ConflictHttpException::class);
        $this->expectExceptionMessage('Resource should be Group');

        $processor = new InvitedByProcessor(
            $this->createMock(GroupRepository::class),
            $this->createMock(MessageBusInterface::class),
        );

        /** @phpstan-ignore-next-line */
        $processor->process(new stdClass(), $this->createMock(Operation::class), []);
    }
}
