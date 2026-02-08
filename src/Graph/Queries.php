<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\ConnectionInterface;
use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;
use Kynx\GraphQLite\ValueObject\Stats;

use function array_map;
use function iterator_to_array;

/**
 * @internal
 *
 * @phpstan-import-type NodeArray from Node
 * @phpstan-import-type EdgeArray from Edge
 */
final readonly class Queries implements QueriesInterface
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    /**
     * Returns the degree (number of connections) of a node.
     */
    public function degree(string $nodeId): int
    {
        /** @var Result<array{degree: int}> $result */
        $result = $this->connection->cypher(
            'MATCH (n {id: $id})-[r]-() RETURN COUNT(r) AS degree',
            ['id' => $nodeId]
        );
        if ($result->count() === 0) {
            return 0;
        }

        return $result->current()['degree'];
    }

    /**
     * Returns nodes connected by edges in either direction.
     *
     * @return Node[]
     */
    public function neighbours(string $nodeId): array
    {
        /** @var Result<array{m: NodeArray}> $result */
        $result = $this->connection->cypher(
            'MATCH (n {id: $id})-[]-(m) RETURN DISTINCT m',
            ['id' => $nodeId]
        );

        return array_map(
            static fn (array $row): Node => Node::fromArray($row['m']),
            iterator_to_array($result),
        );
    }

    /**
     * Returns all edges connected to a node.
     *
     * @return Edge[]
     */
    public function edges(string $nodeId): array
    {
        /** @var Result<EdgeArray> $result */
        $result = $this->connection->cypher(
            'MATCH (n {id: $id})-[r]->(m) RETURN n.id AS sourceId, m.id AS targetId, r ORDER BY m.id',
            ['id' => $nodeId]
        );
        if ($result->count() === 0) {
            return [];
        }

        return array_map(
            static fn (array $row): Edge => Edge::fromArray($row),
            iterator_to_array($result),
        );
    }

    /**
     * Returns statistics about the graph.
     */
    public function stats(): Stats
    {
        /** @var Result<array{cnt: int}> $nodes */
        $nodes = $this->connection->cypher("MATCH (n) RETURN COUNT(n) AS cnt");
        /** @var Result<array{cnt: int}> $edges */
        $edges = $this->connection->cypher("MATCH ()-[r]->() RETURN COUNT(r) AS cnt");

        return new Stats(
            $nodes->count() > 0 ? $nodes->current()['cnt'] : 0,
            $edges->count() > 0 ? $edges->current()['cnt'] : 0,
        );
    }

    public function query(string $cypher, array $params): Result
    {
        return $this->connection->cypher($cypher, $params);
    }
}
