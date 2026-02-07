<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\ValueObject\Edge;

interface EdgesInterface
{
    /**
     * Returns true if an edge exists between two nodes
     */
    public function has(string $sourceId, string $targetId): bool;

    /**
     * Returns an edge between two nodes, if it exists
     */
    public function get(string $sourceId, string $targetId): ?Edge;

    /**
     * Create or update an edge between two nodes
     */
    public function upsert(Edge $edge): void;

    /**
     * Delete an edge between two nodes
     */
    public function delete(string $sourceId, string $targetId): void;

    /**
     * Returns all edges
     *
     * @return Edge[]
     */
    public function getAll(): array;
}
