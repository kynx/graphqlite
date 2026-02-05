<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\ConnectionInterface;
use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\Cypher\Util;
use Kynx\GraphQLite\ValueObject\Edge;

use function array_map;
use function iterator_to_array;
use function sprintf;

/**
 * @internal
 *
 * @phpstan-type EdgeArray = array{type: string, properties: array<string, mixed>}
 */
final readonly class Edges
{
    public function __construct(private ConnectionInterface $connection)
    {
    }

    public function has(string $sourceId, string $targetId): bool
    {
        /** @var Result<array{cnt: int}> $result */
        $result = $this->connection->cypher(sprintf(
            "MATCH (a {id: '%s'})-[r]->(b {id: '%s'}) RETURN COUNT(r) AS cnt",
            Util::escape($sourceId),
            Util::escape($targetId),
        ));

        if ($result->count() === 0) {
            return false;
        }

        return (bool) $result->current()['cnt'];
    }

    public function get(string $sourceId, string $targetId): ?Edge
    {
        /** @var Result<array{r: EdgeArray}> $result */
        $result = $this->connection->cypher(sprintf(
            "MATCH (a {id: '%s'})-[r]->(b {id: '%s'}) RETURN r",
            Util::escape($sourceId),
            Util::escape($targetId)
        ));

        if ($result->count() === 0) {
            return null;
        }

        $r = $result->current()['r'];
        return self::makeEdge($sourceId, $targetId, $r['type'], $r['properties']);
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
        /** @var Result<array{source: string, target: string, r: EdgeArray}> $result */
        $result = $this->connection->cypher(
            "MATCH (a)-[r]->(b) RETURN a.id AS source, b.id AS target, r"
        );

        return array_map(
            static fn (array $row): Edge => self::makeEdge(
                $row['source'],
                $row['target'],
                $row['r']['type'],
                $row['r']['properties']
            ),
            iterator_to_array($result)
        );
    }

    /**
     * @param array<array-key, mixed> $data
     */
    public static function makeEdge(string $sourceId, string $targetId, string $relation, array $data): Edge
    {
        return new Edge($sourceId, $targetId, $relation, $data);
    }
}
