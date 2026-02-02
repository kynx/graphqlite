<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\ValueObject;

use function get_object_vars;

final readonly class Edge
{
    /**
     * @param array<array-key, mixed> $data
     */
    public function __construct(
        public string $sourceId,
        public string $targetId,
        public string $relation = 'RELATED',
        public array $data = []
    ) {
    }

    public function equals(mixed $other): bool
    {
        return $other instanceof Edge
            && Util::propertiesAreEqual(get_object_vars($this), get_object_vars($other));
    }
}
