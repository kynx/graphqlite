<?php

declare(strict_types=1);

namespace Kynx\GqLite\ValueObject;

use function get_object_vars;

final readonly class Traversal
{
    public function __construct(public string $nodeId, public int $depth, public int $order)
    {
    }

    /**
     * @param array{user_id: string, depth: scalar, order: scalar} $traversal
     */
    public static function fromArray(array $traversal): self
    {
        return new self($traversal['user_id'], (int) $traversal['depth'], (int) $traversal['order']);
    }

    public function equals(mixed $other): bool
    {
        if (! $other instanceof self) {
            return false;
        }

        return get_object_vars($this) === get_object_vars($other);
    }
}
