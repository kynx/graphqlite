<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\ConnectionInterface;
use Kynx\GqLite\Cypher\CypherUtil;
use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\ValueObject\Traversal;

use function sprintf;

/**
 * @psalm-type TraversalRow = array{column_0: array{user_id: string, depth: int, order: int}}
 */
final readonly class Traversals implements TraversalsInterface
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function breadthFirst(string $startId, ?int $maxDepth = null): array
    {
        if ($maxDepth === null) {
// Parameters are currently broken with traversals: https://github.com/colliery-io/graphqlite/issues/27
//            $results = $this->connection->cypher(
//                'RETURN bfs($startId)',
//                ['startId' => $startId]
//            );
            /** @var Result<TraversalRow> $results */
            $results = $this->connection->cypher(
                sprintf("RETURN bfs('%s')", CypherUtil::escape($startId))
            );
        } else {
//            $results = $this->connection->cypher(
//                'RETURN bfs($startId, $maxDepth)',
//                ['startId' => $startId, 'maxDepth' => $maxDepth]
//            );
            /** @var Result<TraversalRow> $results */
            $results = $this->connection->cypher(
                sprintf("RETURN bfs('%s', %d)", CypherUtil::escape($startId), $maxDepth)
            );
        }

        return $this->mapResults($results);
    }

    public function depthFirst(string $startId, ?int $maxDepth = null): array
    {
        if ($maxDepth === null) {
//            $results = $this->connection->cypher(
//                'RETURN dfs($startId)',
//                ['startId' => $startId]
//            );
            /** @var Result<TraversalRow> $results */
            $results = $this->connection->cypher(
                sprintf("RETURN dfs('%s')", $startId),
            );
        } else {
//            $results = $this->connection->cypher(
//                'RETURN dfs($startId, $maxDepth)',
//                ['startId' => $startId, 'maxDepth' => $maxDepth]
//            );
            /** @var Result<TraversalRow> $results */
            $results = $this->connection->cypher(
                sprintf("RETURN dfs('%s', %d)", $startId, $maxDepth),
            );
        }

        return $this->mapResults($results);
    }

    /**
     * @param Result<TraversalRow> $results
     * @return list<Traversal>
     */
    private function mapResults(Result $results): array
    {
        $traversals = [];
        $current    = $results->current();
        foreach ($current['column_0'] as $row) {
            if (! isset($row['user_id'])) {
                continue;
            }

            $traversals[] = Traversal::fromArray($row);
        }

        return $traversals;
    }
}
