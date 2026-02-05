<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\ConnectionInterface;
use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\Cypher\Util;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;
use Kynx\GraphQLite\ValueObject\Stats;

use function array_map;
use function iterator_to_array;
use function sprintf;

/**
 * @internal
 *
 * @phpstan-import-type NodeArray from Nodes
 * @phpstan-import-type EdgeArray from Edges
 */
final readonly class Queries
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function degree(string $nodeId): int
    {
        /** @var Result<array{degree: int}> $result */
        $result = $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'})-[r]-() RETURN COUNT(r) AS degree",
            Util::escape($nodeId)
        ));
        if ($result->count() === 0) {
            return 0;
        }

        return $result->current()['degree'];
    }

    /**
     * @return Node[]
     */
    public function neighbours(string $nodeId): array
    {
        /** @var Result<array{m: NodeArray}> $result */
        $result = $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'})-[]-(m) RETURN DISTINCT m",
            Util::escape($nodeId)
        ));

        return array_map(
            static fn (array $row): Node => Nodes::makeNode($row['m']),
            iterator_to_array($result),
        );
    }

    /**
     * @return Edge[]
     */
    public function edges(string $nodeId): array
    {
        /** @var Result<array{source: string, target: string, r: EdgeArray}> $result */
        $result = $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'})-[r]->(m) RETURN n.id AS source, m.id AS target, r ORDER BY m.id",
            Util::escape($nodeId)
        ));
        if ($result->count() === 0) {
            return [];
        }

        return array_map(
            static fn (array $row): Edge => Edges::makeEdge(
                $row['source'],
                $row['target'],
                $row['r']['type'],
                $row['r']['properties']
            ),
            iterator_to_array($result),
        );
    }

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

    public function query(string $cypher): Result
    {
        return $this->connection->cypher($cypher);
    }
}
