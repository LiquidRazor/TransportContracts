<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Tests\Metadata;

use InvalidArgumentException;
use LiquidRazor\TransportContracts\Metadata\ArrayTransportMetadata;
use PHPUnit\Framework\TestCase;

final class ArrayTransportMetadataTest extends TestCase
{
    public function testHasUsesKeyExistenceInsteadOfTruthiness(): void
    {
        $metadata = new ArrayTransportMetadata([
            'null' => null,
            'false' => false,
            'zero' => 0,
        ]);

        self::assertTrue($metadata->has('null'));
        self::assertTrue($metadata->has('false'));
        self::assertTrue($metadata->has('zero'));
        self::assertFalse($metadata->has('missing'));
    }

    public function testGetReturnsStoredValueForExistingKey(): void
    {
        $metadata = new ArrayTransportMetadata(['contentType' => 'application/json']);

        self::assertSame('application/json', $metadata->get('contentType'));
    }

    public function testGetReturnsDefaultForMissingKey(): void
    {
        $metadata = new ArrayTransportMetadata();

        self::assertSame('fallback', $metadata->get('missing', 'fallback'));
    }

    public function testWithReturnsNewInstanceAndPreservesOriginal(): void
    {
        $original = new ArrayTransportMetadata(['traceId' => 'abc']);
        $updated = $original->with('correlationId', 'def');

        self::assertNotSame($original, $updated);
        self::assertSame(['traceId' => 'abc'], $original->all());
        self::assertSame(['traceId' => 'abc', 'correlationId' => 'def'], $updated->all());
    }

    public function testWithoutReturnsNewInstanceAndPreservesOriginal(): void
    {
        $original = new ArrayTransportMetadata([
            'traceId' => 'abc',
            'correlationId' => 'def',
        ]);

        $updated = $original->without('correlationId');

        self::assertNotSame($original, $updated);
        self::assertSame(['traceId' => 'abc', 'correlationId' => 'def'], $original->all());
        self::assertSame(['traceId' => 'abc'], $updated->all());
    }

    public function testConstructorRejectsNonStringKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new ArrayTransportMetadata([1 => 'invalid']);
    }
}
