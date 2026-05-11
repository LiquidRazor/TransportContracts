<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

interface TransportInputInterface
{
    public function transportName(): string;

    public function operationName(): ?string;

    public function payload(): mixed;

    public function metadata(): TransportMetadataInterface;
}
