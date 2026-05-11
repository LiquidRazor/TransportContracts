<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Exception;

use InvalidArgumentException;

final class UnsupportedTransportInputException extends InvalidArgumentException implements TransportExceptionInterface
{
}
