<?php

declare(strict_types=1);

namespace Kynx\GqLite;

use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\Graph\EdgesInterface;
use Kynx\GqLite\Graph\NodesInterface;
use Kynx\GqLite\ValueObject\Stats;

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
     *
     * @param array<string, null|scalar> $params
     */
    public function query(string $cypher, array $params = []): Result;

    /**
     * Returns statistics about the graph.
     */
    public function stats(): Stats;
}
// phpcs:enable
