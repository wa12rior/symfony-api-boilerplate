<?php

declare(strict_types=1);

namespace App\Common\CQRS;

use Symfony\Component\Uid\Uuid;

abstract class TraceableMessage
{
    public function __construct(
        protected ?string $traceId = null
    ) {
        if (!$this->traceId) {
            $this->traceId = Uuid::v4()->jsonSerialize();
        }
    }

    public function getTraceId(): ?string
    {
        return $this->traceId;
    }
}
