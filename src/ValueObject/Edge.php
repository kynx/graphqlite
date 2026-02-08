<?php

declare(strict_types=1);

namespace Kynx\GqLite\ValueObject;

use Kynx\GqLite\Cypher\CypherUtil;

use function get_object_vars;

/**
 * @phpstan-type EdgeArray = array{
 *     sourceId: string,
 *     targetId: string,
 *     r:        array{
 *         type:       string,
 *         properties: array<string, mixed>,
 *     },
 * }
 */
final readonly class Edge
{
    /** @var non-empty-string */
    public string $relation;
    /** @var array<string, null|scalar> */
    public array $properties;

    /**
     * @param array<array-key, mixed> $properties
     */
    public function __construct(
        public string $sourceId,
        public string $targetId,
        string $relation = 'RELATED',
        array $properties = []
    ) {
        CypherUtil::validateIdentifier($relation);
        CypherUtil::validateProperties($properties);

        $this->relation   = $relation;
        $this->properties = $properties;
    }

    /**
     * @param EdgeArray $edge
     */
    public static function fromArray(array $edge): Edge
    {
        return new Edge($edge['sourceId'], $edge['targetId'], $edge['r']['type'], $edge['r']['properties']);
    }

    public function equals(mixed $other): bool
    {
        return $other instanceof Edge
            && Util::propertiesAreEqual(get_object_vars($this), get_object_vars($other));
    }
}
