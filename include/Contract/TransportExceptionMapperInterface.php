<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

interface TransportExceptionMapperInterface
{
    public function map(\Throwable $throwable, TransportContextInterface $context): TransportOutputInterface;
}
