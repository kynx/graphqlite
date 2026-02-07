<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;
use Kynx\GraphQLite\ValueObject\Stats;

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
     */
    public function query(string $cypher): Result;
}
