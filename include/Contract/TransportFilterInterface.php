<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

interface TransportFilterInterface
{
    public function process(
        TransportInputInterface $input,
        TransportContextInterface $context,
        TransportPipelineInterface $next
    ): TransportOutputInterface;
}
