<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

interface TransportPipelineInterface
{
    public function process(
        TransportInputInterface $input,
        TransportContextInterface $context
    ): TransportOutputInterface;
}
