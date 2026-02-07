<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite\ValueObject;

use Kynx\GraphQLite\Exception\InvalidIdentifierException;
use Kynx\GraphQLite\Exception\InvalidPropertyException;
use Kynx\GraphQLite\ValueObject\Node;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Node::class)]
final class NodeTest extends TestCase
{
    public function testConstructorThrowsExceptionWhenDataContainsId(): void
    {
        self::expectException(InvalidPropertyException::class);
        self::expectExceptionMessage('Properties cannot contain an "id" key');

        new Node('123', ['id' => '123']);
    }

    public function testConstructorValidatesProperties(): void
    {
        self::expectException(InvalidIdentifierException::class);
        self::expectExceptionMessage("'1abc' is not a valid Cypher identifier");

        new Node('123', ['1abc' => 'value']);
    }

    public function testConstructorValidatesLabels(): void
    {
        self::expectException(InvalidIdentifierException::class);
        self::expectExceptionMessage("'label' identifier is required");

        new Node('123', []);
    }

    #[DataProvider('equalsProvider')]
    public function testEquals(Node $node, mixed $other, bool $expected): void
    {
        $actual = $node->equals($other);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{Node, mixed, bool}>
     */
    public static function equalsProvider(): array
    {
        $node = new Node('123', ['a' => 'b'], 'Entity');
        return [
            'same node'            => [$node, $node, true],
            'equal node'           => [$node, new Node('123', ['a' => 'b'], 'Entity'), true],
            'not node'             => [$node, new stdClass(), false],
            'properties not equal' => [$node, new Node('123', ['a' => 'c'], 'Entity'), false],
            'labels not equal'     => [$node, new Node('123', ['a' => 'b'], 'Other'), false],
        ];
    }

    public function testWithPropertiesReturnsNewInstance(): void
    {
        $expected = ['bar' => 'baz'];
        $node     = new Node('test', ['foo' => 'bar'], 'Test');

        $actual = $node->withProperties($expected);
        self::assertNotSame($node, $actual);
        self::assertSame($node->id, $actual->id);
        self::assertSame($node->labels, $actual->labels);
        self::assertSame($expected, $actual->properties);
    }

    public function testWithLabelsReturnsNewInstance(): void
    {
        $expected = ['B', 'C'];
        $node     = new Node('test', ['foo' => 'bar'], 'Test');

        $actual = $node->withLabels(...$expected);
        self::assertNotSame($node, $actual);
        self::assertSame($node->id, $actual->id);
        self::assertSame($node->properties, $actual->properties);
        self::assertSame($expected, $actual->labels);
    }
}
