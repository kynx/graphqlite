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

    public function testHasFiltersRelation(): void
    {
        $expected = new Edge('cat', 'mouse', 'EATS', ['meal' => 'breakfast']);
        $this->nodes->upsert(new Node('cat', [], 'Feline'));
        $this->nodes->upsert(new Node('mouse', [], 'Rodent'));
        $this->edges->upsert($expected);

        $eats      = $this->edges->has('cat', 'mouse', 'EATS');
        $playsWith = $this->edges->has('cat', 'mouse', 'PLAYS_WITH');
        self::assertTrue($eats);
        self::assertFalse($playsWith);
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
        self::assertEquals($expected, $actual);
    }

    public function testGetFiltersRelation(): void
    {
        $this->nodes->upsert(new Node('cat', [], 'Feline'));
        $this->nodes->upsert(new Node('mouse', [], 'Rodent'));
        $this->edges->upsert(new Edge('cat', 'mouse', 'PLAYS_WITH', ['time' => '1 hour']));
        $this->edges->upsert(new Edge('cat', 'mouse', 'EATS', ['meal' => 'breakfast']));

        $playsWith = $this->edges->get('cat', 'mouse', 'PLAYS_WITH');
        self::assertInstanceOf(Edge::class, $playsWith);
        self::assertSame('PLAYS_WITH', $playsWith->relation);
        self::assertSame(['time' => '1 hour'], $playsWith->properties);

        $eats = $this->edges->get('cat', 'mouse', 'EATS');
        self::assertInstanceOf(Edge::class, $eats);
        self::assertSame('EATS', $eats->relation);
        self::assertSame(['meal' => 'breakfast'], $eats->properties);
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

    public function testDeleteRemovesRelation(): void
    {
        $this->nodes->upsert(new Node('cat', [], 'Feline'));
        $this->nodes->upsert(new Node('mouse', [], 'Rodent'));
        $this->edges->upsert(new Edge('cat', 'mouse', 'PLAYS_WITH', ['time' => '1 hour']));
        $this->edges->upsert(new Edge('cat', 'mouse', 'EATS', ['meal' => 'breakfast']));

        $actual = $this->edges->getAll();
        self::assertCount(2, $actual);

        $this->edges->delete('cat', 'mouse', 'PLAYS_WITH');
        $actual = $this->edges->getAll();
        self::assertCount(1, $actual);
        self::assertSame('EATS', $actual[0]->relation);
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
