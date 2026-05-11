<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

interface TransportEmitterInterface
{
    public function emit(TransportOutputInterface $output): mixed;
}
