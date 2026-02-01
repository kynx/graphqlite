<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite\ValueObject;

use Kynx\GraphQLite\ValueObject\Edge;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Edge::class)]
final class EdgeTest extends TestCase
{
    #[DataProvider('equalsProvider')]
    public function testEquals(Edge $edge, mixed $other, bool $expected): void
    {
        $actual = $edge->equals($other);
        self::assertSame($expected, $actual);
    }

    public static function equalsProvider(): array
    {
        $edge = new Edge('aaa', 'bbb', 'PARENT', ['a' => 'b']);
        return [
            'same edge' => [$edge, $edge, true],
            'equal edge' => [$edge, new Edge('aaa', 'bbb', 'PARENT', ['a' => 'b']), true],
            'not edge' => [$edge, new stdClass(), false],
            'not equal' => [$edge, new Edge('aaa', 'bbb', 'PARENT', ['a' => 'c']), false],
        ];
    }
}
