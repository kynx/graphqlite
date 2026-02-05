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
 * @psalm-internal \Kynx\GraphQLite
 * @psalm-internal \KynxTest\GraphQLite
 */
final readonly class Queries
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function degree(string $nodeId): int
    {
        $result = $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'})-[r]-() RETURN count(r) AS degree",
            Util::escape($nodeId)
        ));
        if ($result->count() === 0) {
            return 0;
        }

        return (int) ($result->current()['degree'] ?? 0);
    }

    /**
     * @return Node[]
     */
    public function neighbours(string $nodeId): array
    {
        $result = $this->connection->cypher(sprintf(
            "MATCH (n {id: '%s'})-[]-(m) RETURN DISTINCT m",
            Util::escape($nodeId)
        ));

        return array_map(
            static fn (array $row): Node => Nodes::makeNode($row['m'] ?? []),
            iterator_to_array($result),
        );
    }

    /**
     * @return Edge[]
     */
    public function edges(string $nodeId): array
    {
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
        $nodes = $this->connection->cypher("MATCH (n) RETURN COUNT(n) AS cnt");
        $edges = $this->connection->cypher("MATCH ()-[r]->() RETURN COUNT(r) AS cnt");

        return new Stats(
            (int) ($nodes->count() > 0 ? $nodes->current()['cnt'] ?? 0 : 0),
            (int) ($edges->count() > 0 ? $edges->current()['cnt'] ?? 0 : 0),
        );
    }

    public function query(string $cypher): Result
    {
        return $this->connection->cypher($cypher);
    }
}
