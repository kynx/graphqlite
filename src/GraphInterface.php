<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;

interface GraphInterface
{
    /**
     * Returns true if node with given id exists
     */
    public function hasNode(string $nodeId): bool;

    /**
     * Returns node by id, if it exists
     */
    public function getNode(string $nodeId): ?Node;

    /**
     * Create or update a node
     */
    public function upsertNode(Node $node): void;

    /**
     * Delete a node by id
     */
    public function deleteNode(string $nodeId): void;

    /**
     * Returns all nodes, optionally filtered by label
     *
     * @return Node[]
     */
    public function getAllNodes(string $label): array;

    /**
     * Returns true if an edge exists between two nodes
     */
    public function hasEdge(string $sourceId, string $targetId): bool;

    /**
     * Returns an edge between two nodes, if it exists
     */
    public function getEdge(string $sourceId, string $targetId): ?Edge;

    /**
     * Create or update an edge between two nodes
     */
    public function upsertEdge(Edge $edge): void;

    /**
     * Delete an edge between two nodes
     */
    public function deleteEdge(string $sourceId, string $targetId): void;

    /**
     * Returns all edges
     *
     * @return Edge[]
     */
    public function getAllEdges(): array;
}