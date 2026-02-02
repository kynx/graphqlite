<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Kynx\GraphQLite\Graph\Edges;
use Kynx\GraphQLite\Graph\Nodes;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;

final readonly class Graph implements GraphInterface
{
    private Nodes $nodes;
    private Edges $edges;

    public function __construct(ConnectionInterface $connection)
    {
        $this->nodes = new Nodes($connection);
        $this->edges = new Edges($connection);
    }

    public function hasNode(string $nodeId): bool
    {
        return $this->nodes->has($nodeId);
    }

    public function getNode(string $nodeId): ?Node
    {
        return $this->nodes->get($nodeId);
    }

    public function upsertNode(Node $node, string $label = 'Entity'): void
    {
        $this->nodes->upsert($node, $label);
    }

    public function deleteNode(string $nodeId): void
    {
        $this->nodes->delete($nodeId);
    }

    public function getAllNodes(string $label = ''): array
    {
        return $this->nodes->getAll($label);
    }

    public function hasEdge(string $sourceId, string $targetId): bool
    {
        return $this->edges->has($sourceId, $targetId);
    }

    public function getEdge(string $sourceId, string $targetId): ?Edge
    {
        return $this->edges->get($sourceId, $targetId);
    }

    public function upsertEdge(Edge $edge): void
    {
        $this->edges->upsert($edge);
    }

    public function deleteEdge(string $sourceId, string $targetId): void
    {
        $this->edges->delete($sourceId, $targetId);
    }

    public function getAllEdges(): array
    {
        return $this->edges->getAll();
    }
}
