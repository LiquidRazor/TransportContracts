<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Metadata;

use InvalidArgumentException;
use LiquidRazor\TransportContracts\Contract\TransportMetadataInterface;

final class ArrayTransportMetadata implements TransportMetadataInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $items;

    public function __construct(array $items = [])
    {
        foreach ($items as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidArgumentException('Transport metadata keys must be strings.');
            }
        }

        /** @var array<string, mixed> $items */
        $this->items = $items;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->items);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->items[$key] : $default;
    }

    public function all(): array
    {
        return $this->items;
    }

    public function with(string $key, mixed $value): self
    {
        $items = $this->items;
        $items[$key] = $value;

        return new self($items);
    }

    public function without(string $key): self
    {
        $items = $this->items;
        unset($items[$key]);

        return new self($items);
    }
}
