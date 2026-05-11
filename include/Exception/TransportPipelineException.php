<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Exception;

use RuntimeException;

final class TransportPipelineException extends RuntimeException implements TransportExceptionInterface
{
}
