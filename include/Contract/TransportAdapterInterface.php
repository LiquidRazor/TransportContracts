<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

interface TransportAdapterInterface
{
    public function supports(mixed $nativeInput): bool;

    public function adapt(mixed $nativeInput): TransportInputInterface;
}
