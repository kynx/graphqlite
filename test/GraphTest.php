<?php

declare(strict_types=1);

namespace KynxTest\GqLite;

use Kynx\GqLite\Graph;
use Kynx\GqLite\ValueObject\Edge;
use Kynx\GqLite\ValueObject\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Graph::class)]
final class GraphTest extends TestCase
{
    use ConnectionTrait;

    public function testConnectReturnFunctioningGraph(): void
    {
        $n1            = new Node('n1', ['v' => 1], 'Entity');
        $n2            = new Node('n2', ['v' => 2], 'Entity');
        $e1            = new Edge('n1', 'n2');
        $extensionPath = $this->getExtensionPath();

        $graph = Graph::connect($extensionPath, ':memory:');
        $graph->nodes->upsert($n1);
        $graph->nodes->upsert($n2);
        $graph->edges->upsert($e1);
        $stats = $graph->stats();

        self::assertTrue($graph->nodes->has('n1'));
        self::assertTrue($graph->edges->has('n1', 'n2'));
        self::assertSame(2, $stats->nodes);
    }

    public function testGetInstanceReturnsFunctioningGraph(): void
    {
        $n1         = new Node('n1', ['v' => 1], 'Entity');
        $n2         = new Node('n2', ['v' => 2], 'Entity');
        $e1         = new Edge('n1', 'n2');
        $connection = $this->getConnection();

        $graph = Graph::getInstance($connection);
        $graph->nodes->upsert($n1);
        $graph->nodes->upsert($n2);
        $graph->edges->upsert($e1);
        $stats = $graph->stats();

        self::assertTrue($graph->nodes->has('n1'));
        self::assertTrue($graph->edges->has('n1', 'n2'));
        self::assertCount(2, $graph->traversals->breadthFirst('n1'));
        self::assertSame(2, $stats->nodes);
    }

    public function testQueryProxiesQuery(): void
    {
        $cypher     = "CREATE (n:Persion {id: 'alice', name: 'Alice'})";
        $connection = $this->getConnection();

        $graph = Graph::getInstance($connection);
        $graph->query($cypher);

        $actual = $graph->nodes->get('alice');
        self::assertNotNull($actual);
        self::assertSame('Alice', $actual->properties['name']);
    }
}
