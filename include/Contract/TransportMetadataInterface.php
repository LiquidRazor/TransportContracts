<?php

declare(strict_types=1);

namespace LiquidRazor\TransportContracts\Contract;

interface TransportMetadataInterface
{
    public function has(string $key): bool;

    public function get(string $key, mixed $default = null): mixed;

    public function all(): array;

    public function with(string $key, mixed $value): self;

    public function without(string $key): self;
}
