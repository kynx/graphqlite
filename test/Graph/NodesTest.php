<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite\Graph;

use Kynx\GraphQLite\Graph\Nodes;
use Kynx\GraphQLite\ValueObject\Node;
use KynxTest\GraphQLite\ConnectionTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_map;

#[CoversClass(Nodes::class)]
final class NodesTest extends TestCase
{
    use ConnectionTrait;

    private Nodes $nodes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->nodes = new Nodes($this->getConnection());
    }

    public function testHasReturnsFalse(): void
    {
        $actual = $this->nodes->has('nonexistent');
        self::assertFalse($actual);
    }

    public function testHasReturnsTrue(): void
    {
        $this->nodes->upsert(new Node('test_node', [], 'Test'));

        $actual = $this->nodes->has('test_node');
        self::assertTrue($actual);
    }

    public function testGetReturnsNull(): void
    {
        $actual = $this->nodes->get('nonexistent');
        self::assertNull($actual);
    }

    public function testGetReturnsNode(): void
    {
        $expected = new Node('test_node', ['foo' => 'bar'], 'Test');
        $this->nodes->upsert($expected);

        $actual = $this->nodes->get('test_node');
        self::assertTrue($expected->equals($actual));
    }

    public function testUpsertUpdatesNode(): void
    {
        $expected = new Node('existing', ['foo' => 'baz'], 'Test');
        $existing = new Node('existing', ['foo' => 'bar'], 'Test');
        $this->nodes->upsert($existing);
        $this->nodes->upsert($expected);

        $actual = $this->nodes->get('existing');
        self::assertTrue($expected->equals($actual));
    }

    public function testDeleteRemovesNode(): void
    {
        $this->nodes->upsert(new Node('test_node', [], 'Test'));
        $this->nodes->delete('test_node');

        $actual = $this->nodes->has('test_node');
        self::assertFalse($actual);
    }

    public function testGetAllNodesReturnsAll(): void
    {
        $expected = ['n1', 'n2', 'n3'];
        $this->nodes->upsert(new Node('n1', ['v' => 1], 'Number'));
        $this->nodes->upsert(new Node('n2', ['v' => 2], 'Number'));
        $this->nodes->upsert(new Node('n3', ['v' => 'a'], 'String'));

        $actual = array_map(static fn (Node $node): string => $node->id, $this->nodes->getAll());
        self::assertSame($expected, $actual);
    }

    public function testGetAllNodesReturnsMatchingLabels(): void
    {
        $expected = ['n1', 'n2'];
        $this->nodes->upsert(new Node('n1', ['v' => 1], 'Number'));
        $this->nodes->upsert(new Node('n2', ['v' => 2], 'Number'));
        $this->nodes->upsert(new Node('n3', ['v' => 'a'], 'String'));

        $actual = array_map(static fn (Node $node): string => $node->id, $this->nodes->getAll('Number'));
        self::assertSame($expected, $actual);
    }
}
