<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Graph;

use Kynx\GqLite\Graph\Edges;
use Kynx\GqLite\Graph\Nodes;
use Kynx\GqLite\Graph\Traversals;
use Kynx\GqLite\ValueObject\Edge;
use Kynx\GqLite\ValueObject\Node;
use Kynx\GqLite\ValueObject\Traversal;
use KynxTest\GqLite\ConnectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Traversals::class)]
final class TraversalsTest extends TestCase
{
    use ConnectionTrait;

    private Nodes $nodes;
    private Edges $edges;
    private Traversals $traversals;

    protected function setUp(): void
    {
        $connection = $this->getConnection();

        $this->nodes      = new Nodes($connection);
        $this->edges      = new Edges($connection);
        $this->traversals = new Traversals($connection);
    }

    public function testBreadthFirstEmptyGraph(): void
    {
        $expected = [];

        $actual = $this->traversals->breadthFirst('A');
        self::assertSame($expected, $actual);
    }

    public function testBreadthFirstSingleNode(): void
    {
        $expected = [new Traversal('A', 0, 0)];
        $this->nodes->upsert(new Node('A', [], 'Test'));

        $actual = $this->traversals->breadthFirst('A');
        self::assertEquals($expected, $actual);
    }

    public function testBreadthFirstLinearPath(): void
    {
        $expected = [
            new Traversal('A', 0, 0),
            new Traversal('B', 1, 1),
            new Traversal('C', 2, 2),
        ];

        $this->nodes->upsert(new Node('A', [], 'Test'));
        $this->nodes->upsert(new Node('B', [], 'Test'));
        $this->nodes->upsert(new Node('C', [], 'Test'));
        $this->edges->upsert(new Edge('A', 'B'));
        $this->edges->upsert(new Edge('B', 'C'));

        $actual = $this->traversals->breadthFirst('A');
        self::assertEquals($expected, $actual);
    }

    public function testBreadthFirstNonLinearPath(): void
    {
        $expected = [
            new Traversal('A', 0, 0),
            new Traversal('B', 1, 1),
            new Traversal('D', 1, 2),
            new Traversal('C', 2, 3),
        ];

        $this->nodes->upsert(new Node('A', [], 'Test'));
        $this->nodes->upsert(new Node('B', [], 'Test'));
        $this->nodes->upsert(new Node('C', [], 'Test'));
        $this->nodes->upsert(new Node('D', [], 'Test'));
        $this->edges->upsert(new Edge('A', 'B'));
        $this->edges->upsert(new Edge('B', 'C'));
        $this->edges->upsert(new Edge('A', 'D'));

        $actual = $this->traversals->breadthFirst('A');
        self::assertEquals($expected, $actual);
    }

    public function testBreadthFirstMaxDepth(): void
    {
        $expected = [
            new Traversal('A', 0, 0),
            new Traversal('B', 1, 1),
        ];

        $this->nodes->upsert(new Node('A', [], 'Test'));
        $this->nodes->upsert(new Node('B', [], 'Test'));
        $this->nodes->upsert(new Node('C', [], 'Test'));
        $this->edges->upsert(new Edge('A', 'B'));
        $this->edges->upsert(new Edge('B', 'C'));

        $actual = $this->traversals->breadthFirst('A', 1);
        self::assertEquals($expected, $actual);
    }

    public function testDepthFirstEmptyGraph(): void
    {
        $actual = $this->traversals->depthFirst('A');
        self::assertSame([], $actual);
    }

    public function testDepthFirstSingleNode(): void
    {
        $expected = [new Traversal('A', 0, 0)];
        $this->nodes->upsert(new Node('A', [], 'Test'));

        $actual = $this->traversals->depthFirst('A');
        self::assertEquals($expected, $actual);
    }

    public function testDepthFirstLinearPath(): void
    {
        $expected = [
            new Traversal('A', 0, 0),
            new Traversal('B', 1, 1),
            new Traversal('C', 2, 2),
        ];

        $this->nodes->upsert(new Node('A', [], 'Test'));
        $this->nodes->upsert(new Node('B', [], 'Test'));
        $this->nodes->upsert(new Node('C', [], 'Test'));
        $this->edges->upsert(new Edge('A', 'B'));
        $this->edges->upsert(new Edge('B', 'C'));

        $actual = $this->traversals->depthFirst('A');
        self::assertEquals($expected, $actual);
    }

    public function testDepthFirstNonLinearPath(): void
    {
        $expected = [
            new Traversal('A', 0, 0),
            new Traversal('B', 1, 1),
            new Traversal('C', 2, 2),
            new Traversal('D', 1, 3),
        ];

        $this->nodes->upsert(new Node('A', [], 'Test'));
        $this->nodes->upsert(new Node('B', [], 'Test'));
        $this->nodes->upsert(new Node('C', [], 'Test'));
        $this->nodes->upsert(new Node('D', [], 'Test'));
        $this->edges->upsert(new Edge('A', 'B'));
        $this->edges->upsert(new Edge('B', 'C'));
        $this->edges->upsert(new Edge('A', 'D'));

        $actual = $this->traversals->depthFirst('A');
        self::assertEquals($expected, $actual);
    }

    public function testDepthFirstMaxDepth(): void
    {
        $expected = [
            new Traversal('A', 0, 0),
            new Traversal('B', 1, 1),
        ];

        $this->nodes->upsert(new Node('A', [], 'Test'));
        $this->nodes->upsert(new Node('B', [], 'Test'));
        $this->nodes->upsert(new Node('C', [], 'Test'));
        $this->edges->upsert(new Edge('A', 'B'));
        $this->edges->upsert(new Edge('B', 'C'));

        $actual = $this->traversals->depthFirst('A', 1);
        self::assertEquals($expected, $actual);
    }
}
