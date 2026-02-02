<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\ConnectionInterface;
use Kynx\GraphQLite\Cypher\Util;
use Kynx\GraphQLite\ValueObject\Edge;

use function assert;
use function is_array;
use function is_string;
use function sprintf;

/**
 * @internal
 *
 * @psalm-internal \Kynx\GraphQLite
 * @psalm-internal \KynxTest\GraphQLite
 */
final readonly class Edges
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function has(string $sourceId, string $targetId): bool
    {
        $result = $this->connection->cypher(sprintf(
            "MATCH (a {id: '%s'})-[r]->(b {id: '%s'}) RETURN COUNT(r) AS cnt",
            Util::escape($sourceId),
            Util::escape($targetId),
        ));

        if ($result->count() === 0) {
            return false;
        }

        return (int) ($result->current()['cnt'] ?? 0) > 0;
    }

    public function get(string $sourceId, string $targetId): ?Edge
    {
        $result = $this->connection->cypher(sprintf(
            "MATCH (a {id: '%s'})-[r]->(b {id: '%s'}) RETURN r",
            Util::escape($sourceId),
            Util::escape($targetId)
        ));

        if ($result->count() === 0) {
            return null;
        }

        $row      = $result->current();
        $relation = $row['r']['type'] ?? '';
        $data     = $row['r']['properties'] ?? [];

        assert(is_string($relation) && is_array($data));

        return $this->makeEdge($sourceId, $targetId, $relation, $data);
    }

    public function upsert(Edge $edge): void
    {
        if ($this->has($edge->sourceId, $edge->targetId)) {
            return;
        }

        $relationType = Util::sanitizeRelationType($edge->relation);

        if ($edge->data === []) {
            $this->connection->cypher(sprintf(
                "MATCH (a {id: '%s'}), (b {id: '%s'}) CREATE (a)-[r:%s]->(b)",
                Util::escape($edge->sourceId),
                Util::escape($edge->targetId),
                Util::escape($relationType)
            ));
            return;
        }

        $this->connection->cypher(sprintf(
            "MATCH (a {id: '%s'}), (b {id: '%s'}) CREATE (a)-[r:%s {%s}]->(b)",
            Util::escape($edge->sourceId),
            Util::escape($edge->targetId),
            Util::escape($relationType),
            Util::formatProperties($edge->data),
        ));
    }

    public function delete(string $sourceId, string $targetId): void
    {
        $this->connection->cypher(sprintf(
            "MATCH (a {id: '%s'})-[r]->(b {id: '%s'}) DELETE r",
            Util::escape($sourceId),
            Util::escape($targetId)
        ));
    }

    /**
     * @return Edge[]
     */
    public function getAll(): array
    {
        $result = $this->connection->cypher(
            "MATCH (a)-[r]->(b) RETURN a.id AS source, b.id AS target, r"
        );

        $edges = [];
        foreach ($result as $row) {
            $source   = $row['source'] ?? '';
            $target   = $row['target'] ?? '';
            $relation = $row['r']['type'] ?? '';
            $data     = $row['r']['properties'] ?? [];

            assert(is_string($source) && is_string($target) && is_string($relation) && is_array($data));

            $edges[] = $this->makeEdge($source, $target, $relation, $data);
        }

        return $edges;
    }

    /**
     * @param array<array-key, mixed> $data
     */
    private function makeEdge(string $sourceId, string $targetId, string $relation, array $data): Edge
    {
        return new Edge($sourceId, $targetId, $relation, $data);
    }
}
