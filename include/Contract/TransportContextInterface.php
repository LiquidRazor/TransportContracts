<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

use DateTimeImmutable;

interface TransportContextInterface
{
    public function transportName(): string;

    public function operationName(): ?string;

    public function correlationId(): ?string;

    public function causationId(): ?string;

    public function traceId(): ?string;

    public function startedAt(): ?DateTimeImmutable;

    public function deadlineAt(): ?DateTimeImmutable;

    public function metadata(): TransportMetadataInterface;
}
