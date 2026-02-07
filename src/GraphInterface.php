<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\Graph\EdgesInterface;
use Kynx\GraphQLite\Graph\NodesInterface;
use Kynx\GraphQLite\ValueObject\Stats;

// phpcs:disable Internal.ParseError.InterfaceHasMemberVar
interface GraphInterface
{
    /**
     * CRUD operations for nodes
     */
    public NodesInterface $nodes { get; }
    /**
     * CRUD operations for edges
     */
    public EdgesInterface $edges { get; }

    /**
     * Returns result of raw cypher query
     */
    public function query(string $cypher): Result;

    /**
     * Returns statistics about the graph.
     */
    public function stats(): Stats;
}
// phpcs:enable
