<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Graph;

use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\Graph\Edges;
use Kynx\GqLite\Graph\Nodes;
use Kynx\GqLite\Graph\Queries;
use Kynx\GqLite\ValueObject\Edge;
use Kynx\GqLite\ValueObject\Node;
use Kynx\GqLite\ValueObject\Stats;
use KynxTest\GqLite\ConnectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Queries::class)]
final class QueriesTest extends TestCase
{
    use ConnectionTrait;

    private Nodes $nodes;
    private Edges $edges;
    private Queries $queries;

    protected function setUp(): void
    {
        parent::setUp();

        $connection    = $this->getConnection();
        $this->nodes   = new Nodes($connection);
        $this->edges   = new Edges($connection);
        $this->queries = new Queries($connection);
    }

    public function testDegreeWithNonExistentNodeReturnsZero(): void
    {
        $degree = $this->queries->degree('nonexistent');
        $this->assertSame(0, $degree);
    }

    public function testDegreeWithNoConnectionsReturnsZero(): void
    {
        $this->nodes->upsert(new Node('hub', ['name' => 'Hub'], 'Node'));

        $degree = $this->queries->degree('hub');
        self::assertSame(0, $degree);
    }

    public function testDegreeReturnsNumberOfConnections(): void
    {
        $expected = 3;
        $this->nodes->upsert(new Node('hub', ['name' => 'Hub'], 'Node'));
        $this->nodes->upsert(new Node('s1', ['name' => 'Spoke1'], 'Node'));
        $this->nodes->upsert(new Node('s2', ['name' => 'Spoke2'], 'Node'));
        $this->nodes->upsert(new Node('s3', ['name' => 'Spoke3'], 'Node'));
        $this->edges->upsert(new Edge('hub', 's1'));
        $this->edges->upsert(new Edge('hub', 's2'));
        $this->edges->upsert(new Edge('hub', 's3'));

        $actual = $this->queries->degree('hub');
        self::assertSame($expected, $actual);
    }

    public function testNeighboursWithNonExistentNodeReturnsEmptyArray(): void
    {
        $result = $this->queries->neighbours('nonexistent');
        self::assertSame([], $result);
    }

    public function testNeighboursWithNoConnectionsReturnsEmptyArray(): void
    {
        $this->nodes->upsert(new Node('hub', ['name' => 'Hub'], 'Node'));

        $result = $this->queries->neighbours('hub');
        self::assertSame([], $result);
    }

    public function testEdgesWithNonExistentNodeReturnsEmptyArray(): void
    {
        $result = $this->queries->edges('nonexistent');
        self::assertSame([], $result);
    }

    public function testEdgesWithNoConnectionsReturnsEmptyArray(): void
    {
        $this->nodes->upsert(new Node('hub', ['name' => 'Hub'], 'Node'));

        $result = $this->queries->edges('hub');
        self::assertSame([], $result);
    }

    public function testEdgesReturnsEdges(): void
    {
        $expected = [
            new Edge('hub', 'n1', 'UP', ['a' => 'b']),
            new Edge('hub', 'n2', 'DOWN', ['c' => 'd']),
        ];
        $this->nodes->upsert(new Node('hub', ['name' => 'Hub'], 'Node'));
        $this->nodes->upsert(new Node('n1', ['name' => 'Node1'], 'Node'));
        $this->nodes->upsert(new Node('n2', ['name' => 'Node2'], 'Node'));
        $this->edges->upsert($expected[0]);
        $this->edges->upsert($expected[1]);

        $actual = $this->queries->edges('hub');
        self::assertEquals($expected, $actual);
    }

    public function testNeighboursReturnsNodes(): void
    {
        $expected = [
            new Node('nb1', ['name' => 'Neighbour1'], 'Node'),
            new Node('nb2', ['name' => 'Neighbour2'], 'Node'),
        ];
        $this->nodes->upsert(new Node('center', ['name' => 'Center'], 'Node'));
        $this->nodes->upsert($expected[0]);
        $this->nodes->upsert($expected[1]);
        $this->edges->upsert(new Edge('center', 'nb1'));
        $this->edges->upsert(new Edge('center', 'nb2'));

        $actual = $this->queries->neighbours('center');
        self::assertEquals($expected, $actual);
    }

    public function testStatsOnEmptyGraphReturnsZeros(): void
    {
        $expected = new Stats(0, 0);

        $actual = $this->queries->stats();
        self::assertEquals($expected, $actual);
    }

    public function testStatsReturnsNodesAndEdges(): void
    {
        $expected = new Stats(3, 2);
        $this->nodes->upsert(new Node('n1', ['v' => 1], 'Node'));
        $this->nodes->upsert(new Node('n2', ['v' => 2], 'Node'));
        $this->nodes->upsert(new Node('n3', ['v' => 3], 'Node'));
        $this->edges->upsert(new Edge('n1', 'n2'));
        $this->edges->upsert(new Edge('n2', 'n3'));

        $actual = $this->queries->stats();
        self::assertEquals($expected, $actual);
    }

    public function testQueryReturnsResult(): void
    {
        $expected = new Result([['cnt' => 1]], ['cnt']);
        $this->nodes->upsert(new Node('n1', ['v' => 1], 'Node'));

        $actual = $this->queries->query('MATCH (n) RETURN COUNT(n) AS cnt', []);
        self::assertEquals($expected, $actual);
    }
}
