<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite;

use Kynx\GraphQLite\Graph;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Graph::class)]
final class GraphTest extends TestCase
{
    use ConnectionTrait;

    public function testConnectReturnFunctioningGraph(): void
    {
        $n1            = new Node('n1', ['v' => 1]);
        $n2            = new Node('n2', ['v' => 2]);
        $e1            = new Edge('n1', 'n2');
        $extensionPath = $this->getExtensionPath();

        $graph = Graph::connect($extensionPath, ':memory:');
        $graph->nodes->upsert($n1, 'Node');
        $graph->nodes->upsert($n2, 'Node');
        $graph->edges->upsert($e1);
        $stats = $graph->queries->stats();

        self::assertTrue($graph->nodes->has('n1'));
        self::assertTrue($graph->edges->has('n1', 'n2'));
        self::assertSame(2, $stats->nodes);
    }

    public function testGetInstanceReturnsFunctioningGraph(): void
    {
        $n1         = new Node('n1', ['v' => 1]);
        $n2         = new Node('n2', ['v' => 2]);
        $e1         = new Edge('n1', 'n2');
        $connection = $this->getConnection();

        $graph = Graph::getInstance($connection);
        $graph->nodes->upsert($n1, 'Node');
        $graph->nodes->upsert($n2, 'Node');
        $graph->edges->upsert($e1);
        $stats = $graph->queries->stats();

        self::assertTrue($graph->nodes->has('n1'));
        self::assertTrue($graph->edges->has('n1', 'n2'));
        self::assertSame(2, $stats->nodes);
    }
}
