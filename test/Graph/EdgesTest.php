<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Graph;

use Kynx\GqLite\Graph\Edges;
use Kynx\GqLite\Graph\Nodes;
use Kynx\GqLite\ValueObject\Edge;
use Kynx\GqLite\ValueObject\Node;
use KynxTest\GqLite\ConnectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Edges::class)]
final class EdgesTest extends TestCase
{
    use ConnectionTrait;

    private Nodes $nodes;
    private Edges $edges;

    protected function setUp(): void
    {
        parent::setUp();

        $connection  = $this->getConnection();
        $this->nodes = new Nodes($connection);
        $this->edges = new Edges($connection);
    }

    public function testHasReturnsFalse(): void
    {
        $this->nodes->upsert(new Node('node_1', [], 'Test'));
        $this->nodes->upsert(new Node('node_2', [], 'Test'));
        $actual = $this->edges->has('node_1', 'node_2');
        self::assertFalse($actual);
    }

    public function testHasReturnsTrue(): void
    {
        $this->nodes->upsert(new Node('node_1', ['a' => 'b'], 'Test'));
        $this->nodes->upsert(new Node('node_2', ['a' => 'b'], 'Test'));
        $this->edges->upsert(new Edge('node_1', 'node_2'));

        $actual = $this->edges->has('node_1', 'node_2');
        $this->assertTrue($actual);
    }

    public function testGetReturnsNull(): void
    {
        $this->nodes->upsert(new Node('node_1', [], 'Test'));
        $this->nodes->upsert(new Node('node_2', [], 'Test'));

        $actual = $this->edges->get('node_1', 'node_2');
        self::assertNull($actual);
    }

    public function testGetReturnsEdge(): void
    {
        $expected = new Edge('cat', 'mouse', 'EATS', ['meal' => 'breakfast']);
        $this->nodes->upsert(new Node('cat', [], 'Feline'));
        $this->nodes->upsert(new Node('mouse', [], 'Rodent'));
        $this->edges->upsert($expected);

        $actual = $this->edges->get('cat', 'mouse');
        self::assertTrue($expected->equals($actual));
    }

    public function testDeleteRemovesEdge(): void
    {
        $this->nodes->upsert(new Node('node_1', ['a' => 'b'], 'Test'));
        $this->nodes->upsert(new Node('node_2', ['a' => 'b'], 'Test'));
        $this->edges->upsert(new Edge('node_1', 'node_2'));

        $this->edges->delete('node_1', 'node_2');
        $actual = $this->edges->has('node_1', 'node_2');
        self::assertFalse($actual);
    }

    public function testGetAllEdgesReturnsEdges(): void
    {
        $this->nodes->upsert(new Node('node_1', ['a' => 'b'], 'Test'));
        $this->nodes->upsert(new Node('node_2', ['a' => 'b'], 'Test'));
        $this->nodes->upsert(new Node('node_3', ['a' => 'b'], 'Test'));
        $this->edges->upsert(new Edge('node_1', 'node_2'));
        $this->edges->upsert(new Edge('node_2', 'node_3'));

        $actual = $this->edges->getAll();
        self::assertCount(2, $actual);
    }
}
