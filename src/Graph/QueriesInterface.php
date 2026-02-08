<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\ValueObject\Edge;
use Kynx\GqLite\ValueObject\Node;
use Kynx\GqLite\ValueObject\Stats;

interface QueriesInterface
{
    /**
     * Returns the degree (number of connections) of a node.
     */
    public function degree(string $nodeId): int;

    /**
     * Returns nodes connected by edges in either direction.
     *
     * @return Node[]
     */
    public function neighbours(string $nodeId): array;

    /**
     * Returns all edges connected to a node.
     *
     * @return Edge[]
     */
    public function edges(string $nodeId): array;

    /**
     * Returns statistics about the graph.
     */
    public function stats(): Stats;

    /**
     * Returns result of raw cypher query
     *
     * @param array<string, null|scalar> $params
     */
    public function query(string $cypher, array $params): Result;
}
