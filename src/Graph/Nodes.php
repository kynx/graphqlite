<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\ConnectionInterface;
use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\Cypher\Util;
use Kynx\GraphQLite\ValueObject\Node;

use function array_merge;
use function is_array;
use function is_scalar;
use function json_decode;
use function sprintf;

/**
 * @internal
 *
 * @phpstan-type NodeArray = array{properties: array{id?: string, ...}}
 */
final readonly class Nodes
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function has(string $id): bool
    {
        /** @var Result<array{cnt: int}> $result */
        $result = $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'}) RETURN COUNT(n) AS cnt",
            Util::escape($id)
        ));
        if ($result->count() === 0) {
            return false;
        }

        return (bool) $result->current()['cnt'];
    }

    public function get(string $id): ?Node
    {
        /** @var Result<array{n: NodeArray}> $result */
        $result = $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'}) RETURN n",
            Util::escape($id)
        ));
        if ($result->count() === 0) {
            return null;
        }

        $current = $result->current();
        return self::makeNode($current['n']);
    }

    public function upsert(Node $node, string $label): void
    {
        $properties = array_merge(['id' => $node->id], $node->data);

        if (! $this->has($node->id)) {
            $this->connection->cypher(sprintf(
                "CREATE (n:%s {%s})",
                $label,
                Util::formatProperties($properties)
            ));

            return;
        }

        // Cypher supports setting multiple properties in single statement, but GraphQList doesn't :|
        foreach ($properties as $key => $value) {
            $this->connection->cypher(sprintf(
                "MATCH (n {id: '%s'}) SET n.%s = %s RETURN n",
                Util::escape($node->id),
                $key,
                Util::formatProperty($value)
            ));
        }
    }

    public function delete(string $id): void
    {
        $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'}) DETACH DELETE n",
            Util::escape($id)
        ));
    }

    /**
     * @return Node[]
     */
    public function getAll(string $label = ''): array
    {
        if ($label === '') {
            /** @var Result<array{result: string}|array{n: NodeArray}> $result */
            $result = $this->connection->cypher(
                "MATCH (n) RETURN n"
            );
        } else {
            /** @var Result<array{result: string}|array{n: NodeArray}> $result */
            $result = $this->connection->cypher(sprintf(
                "MATCH (n:%s) RETURN n",
                Util::escape($label)
            ));
        }

        $nodes = [];
        foreach ($result as $row) {
            $node = null;
            if (isset($row['result'])) {
                /** @var NodeArray|null|false $node */
                $node = json_decode($row['result'], true);
            } elseif (isset($row['n']) && is_array($row['n'])) {
                $node = $row['n'];
            }

            if (! is_array($node)) {
                continue;
            }

            $nodes[] = self::makeNode($node);
        }

        return $nodes;
    }

    /**
     * @param NodeArray $node
     */
    public static function makeNode(array $node): Node
    {
        $properties = $node['properties'];
        $id         = is_scalar($properties['id'] ?? null) ? (string) $properties['id'] : '';
        unset($properties['id']);

        return new Node($id, $properties);
    }
}
