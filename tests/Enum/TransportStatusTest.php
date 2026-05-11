<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Tests\Enum;

use LiquidRazor\TransportContracts\Enum\TransportStatus;
use PHPUnit\Framework\TestCase;

final class TransportStatusTest extends TestCase
{
    public function testEnumCasesMatchThePlannedNames(): void
    {
        self::assertSame(
            ['Success', 'Failed', 'Rejected', 'Cancelled', 'TimedOut', 'Deferred', 'NoOutput'],
            array_map(
                static fn (TransportStatus $status): string => $status->name,
                TransportStatus::cases()
            )
        );
    }

    public function testEnumValuesMatchThePlannedBackedValues(): void
    {
        self::assertSame(
            ['success', 'failed', 'rejected', 'cancelled', 'timed_out', 'deferred', 'no_output'],
            array_map(
                static fn (TransportStatus $status): string => $status->value,
                TransportStatus::cases()
            )
        );
    }
}
