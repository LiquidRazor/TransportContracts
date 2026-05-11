<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

use LiquidRazor\TransportContracts\Enum\TransportStatus;

interface TransportOutputInterface
{
    public function transportName(): string;

    public function status(): TransportStatus;

    public function payload(): mixed;

    public function metadata(): TransportMetadataInterface;
}
