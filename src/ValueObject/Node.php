<?php

declare(strict_types=1);

namespace Kynx\GqLite\ValueObject;

use Kynx\GqLite\Cypher\CypherUtil;
use Kynx\GqLite\Exception\InvalidPropertyException;

use function array_key_exists;
use function array_unique;
use function get_object_vars;
use function is_scalar;

/**
 * @phpstan-type NodeArray = array{labels: array<string>, properties: array{id?: string, ...}}
 */
final readonly class Node
{
    /** @var array<string, null|scalar> */
    public array $properties;
    /** @var non-empty-array<string> */
    public array $labels;

    /**
     * @param array<array-key, mixed> $properties
     */
    public function __construct(public string $id, array $properties, string ...$labels)
    {
        if (array_key_exists('id', $properties)) {
            throw InvalidPropertyException::propertiesContainsId();
        }

        CypherUtil::validateProperties($properties);
        $this->properties = $properties;

        CypherUtil::validateLabels($labels);
        $this->labels = array_unique($labels);
    }

    /**
     * @param NodeArray $node
     */
    public static function fromArray(array $node): Node
    {
        $properties = $node['properties'];
        $id         = is_scalar($properties['id'] ?? null) ? (string) $properties['id'] : '';
        unset($properties['id']);

        return new Node($id, $properties, ...$node['labels']);
    }

    public function equals(mixed $other): bool
    {
        return $other instanceof Node
            && Util::propertiesAreEqual(get_object_vars($this), get_object_vars($other));
    }

    /**
     * @param array<array-key, mixed> $properties
     */
    public function withProperties(array $properties): self
    {
        return new self($this->id, $properties, ...$this->labels);
    }

    public function withLabels(string ...$labels): self
    {
        return new self($this->id, $this->properties, ...$labels);
    }
}
