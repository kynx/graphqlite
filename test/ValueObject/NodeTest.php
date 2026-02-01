<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite\ValueObject;

use Kynx\GraphQLite\Exception\InvalidArgumentException;
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
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Node data cannot contain an "id" key');

        new Node('123', ['id' => '123']);
    }

    #[DataProvider('equalsProvider')]
    public function testEquals(Node $node, mixed $other, bool $expected): void
    {
        $actual = $node->equals($other);
        self::assertSame($expected, $actual);
    }

    public static function equalsProvider(): array
    {
        $node = new Node('123', ['a' => 'b']);
        return [
            'same node' => [$node, $node, true],
            'equal node' => [$node, new Node('123', ['a' => 'b']), true],
            'not node' => [$node, new stdClass(), false],
            'not equal' => [$node, new Node('123', ['a' => 'c']), false],
        ];
    }
}
