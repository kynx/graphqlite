<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\ValueObject;

use Kynx\GraphQLite\Exception\InvalidArgumentException;

use function array_key_exists;
use function get_object_vars;

final readonly class Node
{
    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(public string $id, public array $data = [])
    {
        if (array_key_exists('id', $data)) {
            throw InvalidArgumentException::dataContainsId();
        }
    }

    public function equals(mixed $other): bool
    {
        return $other instanceof Node
            && Util::propertiesAreEqual(get_object_vars($this), get_object_vars($other));
    }
}
