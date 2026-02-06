<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Graph;

use Kynx\GraphQLite\ValueObject\Node;

/**
 * @internal
 *
 * @phpstan-type NodeArray = array{properties: array{id?: string, ...}}
 */
interface NodesInterface
{
    /**
     * Returns true if node with given id exists
     */
    public function has(string $id): bool;

    /**
     * Returns node by id, if it exists
     */
    public function get(string $id): ?Node;

    /**
     * Create or update a node
     */
    public function upsert(Node $node, string $label): void;

    /**
     * Delete a node by id
     */
    public function delete(string $id): void;

    /**
     * Returns all nodes, optionally filtered by label
     *
     * @return Node[]
     */
    public function getAll(string $label = ''): array;
}
