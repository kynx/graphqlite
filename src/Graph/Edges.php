<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\ConnectionInterface;
use Kynx\GqLite\Cypher\CypherUtil;
use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\ValueObject\Edge;

use function array_map;
use function iterator_to_array;

/**
 * @internal
 *
 * @phpstan-import-type EdgeArray from Edge
 */
final readonly class Edges implements EdgesInterface
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function has(string $sourceId, string $targetId): bool
    {
        /** @var Result<array{cnt: int}> $result */
        $result = $this->connection->cypher(
            'MATCH (a {id: $sourceId})-[r]->(b {id: $targetId}) RETURN COUNT(r) AS cnt',
            ['sourceId' => $sourceId, 'targetId' => $targetId],
        );

        if ($result->count() === 0) {
            return false;
        }

        return (bool) $result->current()['cnt'];
    }

    public function get(string $sourceId, string $targetId): ?Edge
    {
        /** @var Result<EdgeArray> $result */
        $result = $this->connection->cypher(
            'MATCH (a {id: $sourceId})-[r]->(b {id: $targetId}) RETURN a.id AS sourceId, b.id AS targetId, r',
            ['sourceId' => $sourceId, 'targetId' => $targetId],
        );

        if ($result->count() === 0) {
            return null;
        }

        return Edge::fromArray($result->current());
    }

    public function upsert(Edge $edge): void
    {
        if ($this->has($edge->sourceId, $edge->targetId)) {
            return;
        }

        if ($edge->properties === []) {
            $this->connection->cypher(
                "MATCH (a {id: \$sourceId}), (b {id: \$targetId}) CREATE (a)-[r:$edge->relation]->(b)",
                ['sourceId' => $edge->sourceId, 'targetId' => $edge->targetId],
            );
            return;
        }

        $properties = CypherUtil::formatProperties($edge->properties);
        $this->connection->cypher(
            "MATCH (a {id: \$sourceId}), (b {id: \$targetId}) CREATE (a)-[r:$edge->relation $properties]->(b)",
            ['sourceId' => $edge->sourceId, 'targetId' => $edge->targetId],
        );
    }

    public function delete(string $sourceId, string $targetId): void
    {
        $this->connection->cypher(
            "MATCH (a {id: $sourceId})-[r]->(b {id: $targetId}) DELETE r",
            ['sourceId' => $sourceId, 'targetId' => $targetId],
        );
    }

    public function getAll(): array
    {
        /** @var Result<EdgeArray> $result */
        $result = $this->connection->cypher(
            "MATCH (a)-[r]->(b) RETURN a.id AS sourceId, b.id AS targetId, r"
        );

        return array_map(
            static fn (array $row): Edge => Edge::fromArray($row),
            iterator_to_array($result)
        );
    }
}
