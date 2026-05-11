<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Exception;

use RuntimeException;

final class TransportEmissionException extends RuntimeException implements TransportExceptionInterface
{
}
