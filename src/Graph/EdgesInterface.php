<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\ValueObject\Edge;

interface EdgesInterface
{
    /**
     * Returns true if an edge exists between two nodes
     */
    public function has(string $sourceId, string $targetId, ?string $relation = null): bool;

    /**
     * Returns an edge between two nodes, if it exists
     */
    public function get(string $sourceId, string $targetId, ?string $relation = null): ?Edge;

    /**
     * Create or update an edge between two nodes
     *
     * If the edge exists, its properties will be updated.
     */
    public function upsert(Edge $edge): void;

    /**
     * Delete an edge between two nodes
     *
     * If the relation is not specified all edges between the nodes will be deleted.
     */
    public function delete(string $sourceId, string $targetId, ?string $relation = null): void;

    /**
     * Returns all edges
     *
     * @return Edge[]
     */
    public function getAll(): array;
}
