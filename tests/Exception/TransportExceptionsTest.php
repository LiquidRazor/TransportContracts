<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Tests\Exception;

use InvalidArgumentException;
use LiquidRazor\TransportContracts\Exception\TransportEmissionException;
use LiquidRazor\TransportContracts\Exception\TransportExceptionInterface;
use LiquidRazor\TransportContracts\Exception\TransportPipelineException;
use LiquidRazor\TransportContracts\Exception\UnsupportedTransportInputException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TransportExceptionsTest extends TestCase
{
    public function testUnsupportedTransportInputExceptionInheritance(): void
    {
        $exception = new UnsupportedTransportInputException('unsupported');

        self::assertInstanceOf(TransportExceptionInterface::class, $exception);
        self::assertInstanceOf(InvalidArgumentException::class, $exception);
    }

    public function testTransportPipelineExceptionInheritance(): void
    {
        $exception = new TransportPipelineException('pipeline');

        self::assertInstanceOf(TransportExceptionInterface::class, $exception);
        self::assertInstanceOf(RuntimeException::class, $exception);
    }

    public function testTransportEmissionExceptionInheritance(): void
    {
        $exception = new TransportEmissionException('emission');

        self::assertInstanceOf(TransportExceptionInterface::class, $exception);
        self::assertInstanceOf(RuntimeException::class, $exception);
    }
}
