<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite;

use Kynx\GraphQLite\Connection;
use Kynx\GraphQLite\Graph;
use Kynx\GraphQLite\Graph\Edges;
use Kynx\GraphQLite\Graph\Nodes;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function getenv;

#[CoversClass(Graph::class)]
final class GraphTest extends TestCase
{
    private Nodes $nodes;
    private Edges $edges;
    private Graph $graph;

    protected function setUp(): void
    {
        parent::setUp();

        $extensionPath = (string) getenv('GRAPHQLITE_EXTENSION_PATH');
        if ($extensionPath === '') {
            self::fail("GRAPHQLITE_EXTENSION_PATH environment variable not set");
        }

        $connection = Connection::connect($extensionPath, ':memory:');
        $this->nodes = new Nodes($connection);
        $this->edges = new Edges($connection);
        $this->graph = new Graph($connection);
    }

    public function testHasNodeReturnsTrue(): void
    {
        $this->nodes->upsert(new Node('test_node'), 'Test');

        $actual = $this->graph->hasNode('test_node');
        self::assertTrue($actual);
    }

    public function testGetNodeReturnsNode(): void
    {
        $this->nodes->upsert(new Node('test_node'), 'Test');

        $actual = $this->graph->getNode('test_node');
        self::assertNotNull($actual);
    }

    public function testDeleteNodeDeletes(): void
    {
        $this->nodes->upsert(new Node('test_node'), 'Test');
        $this->graph->deleteNode('test_node');

        $actual = $this->graph->hasNode('test_node');
        self::assertFalse($actual);
    }

    public function testGetAllNodesReturnsNodes(): void
    {
        $this->nodes->upsert(new Node('test_node'), 'Test');
        
        $actual = $this->graph->getAllNodes();
        self::assertCount(1, $actual);
    }

    public function testHasEdgeReturnsTrue(): void
    {
        $this->nodes->upsert(new Node('test_source'), 'Test');
        $this->nodes->upsert(new Node('test_target'), 'Test');
        $this->edges->upsert(new Edge('test_source', 'test_target'));

        $actual = $this->graph->hasEdge('test_source', 'test_target');
        self::assertTrue($actual);
    }

    public function testGetEdgeReturnsEdge(): void
    {
        $this->nodes->upsert(new Node('test_source'), 'Test');
        $this->nodes->upsert(new Node('test_target'), 'Test');
        $this->edges->upsert(new Edge('test_source', 'test_target'));

        $actual = $this->graph->getEdge('test_source', 'test_target');
        self::assertInstanceOf(Edge::class, $actual);
    }

    public function testUpsertEdgeCreatesEdge(): void
    {
        $this->nodes->upsert(new Node('test_source'), 'Test');
        $this->nodes->upsert(new Node('test_target'), 'Test');

        $this->graph->upsertEdge(new Edge('test_source', 'test_target'));
        $actual = $this->graph->hasEdge('test_source', 'test_target');
        self::assertTrue($actual);
    }

    public function testDeleteEdgeDeletes(): void
    {
        $this->nodes->upsert(new Node('test_source'), 'Test');
        $this->nodes->upsert(new Node('test_target'), 'Test');
        $this->edges->upsert(new Edge('test_source', 'test_target'));

        $this->graph->deleteEdge('test_source', 'test_target');
        $actual = $this->graph->hasEdge('test_source', 'test_target');
        self::assertFalse($actual);
    }

    public function testGetAllEdgesReturnsEdges(): void
    {
        $this->nodes->upsert(new Node('t1'), 'Test');
        $this->nodes->upsert(new Node('t2'), 'Test');
        $this->nodes->upsert(new Node('t3'), 'Test');
        $this->edges->upsert(new Edge('t1', 't2'));
        $this->edges->upsert(new Edge('t2', 't3'));

        $actual = $this->graph->getAllEdges();
        self::assertCount(2, $actual);
    }
}
