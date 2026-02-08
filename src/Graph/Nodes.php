<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\ConnectionInterface;
use Kynx\GqLite\Cypher\CypherUtil;
use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\ValueObject\Node;

use function implode;
use function is_array;
use function json_decode;

/**
 * @internal
 *
 * @phpstan-import-type NodeArray from Node
 */
final readonly class Nodes implements NodesInterface
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function has(string $id): bool
    {
        /** @var Result<array{cnt: int}> $result */
        $result = $this->connection->cypher(
            'MATCH (n {id: $id}) RETURN COUNT(n) AS cnt',
            ['id' => $id]
        );
        if ($result->count() === 0) {
            return false;
        }

        return (bool) $result->current()['cnt'];
    }

    public function get(string $id): ?Node
    {
        /** @var Result<array{n: NodeArray}> $result */
        $result = $this->connection->cypher(
            'MATCH (n {id: $id}) RETURN n',
            ['id' => $id]
        );
        if ($result->count() === 0) {
            return null;
        }

        $current = $result->current();
        return Node::fromArray($current['n']);
    }

    public function upsert(Node $node): void
    {
        if (! $this->has($node->id)) {
            $labels     = implode(':', $node->labels);
            $properties = CypherUtil::formatProperties(['id' => $node->id, ...$node->properties]);
            $this->connection->cypher("CREATE (n:$labels $properties)");

            return;
        }

        // Cypher supports setting multiple properties in single statement, but GraphQList doesn't :|
        foreach ($node->properties as $key => $value) {
            $property = CypherUtil::formatProperty($value);
            $this->connection->cypher(
                "MATCH (n {id: \$id}) SET n.$key = $property RETURN n",
                ['id' => $node->id],
            );
        }
    }

    public function delete(string $id): void
    {
        $this->connection->cypher(
            'MATCH (n {id: $id}) DETACH DELETE n',
            ['id' => $id]
        );
    }

    public function getAll(string $label = ''): array
    {
        if ($label === '') {
            /** @var Result<array{result: string}|array{n: NodeArray}> $result */
            $result = $this->connection->cypher("MATCH (n) RETURN n");
        } else {
            /** @phpstan-ignore staticMethod.alreadyNarrowedType (Validation does more than type-narrowing!) */
            CypherUtil::validateLabels([$label]);
            /** @var Result<array{result: string}|array{n: NodeArray}> $result */
            $result = $this->connection->cypher("MATCH (n:$label) RETURN n");
        }

        $nodes = [];
        foreach ($result as $row) {
            // This stuff is a direct port of what python does.
            $node = null;
            if (isset($row['result'])) {
                // @fixme When do we get this?
                /** @var NodeArray|null|false $node */
                $node = json_decode($row['result'], true);
            } elseif (isset($row['n']) && is_array($row['n'])) {
                $node = $row['n'];
            }

            if (! is_array($node)) {
                continue;
            }

            $nodes[] = Node::fromArray($node);
        }

        return $nodes;
    }
}
