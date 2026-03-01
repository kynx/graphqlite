<?php

declare(strict_types=1);

namespace KynxTest\GqLite\ValueObject;

use Kynx\GqLite\ValueObject\Traversal;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Traversal::class)]
final class TraversalTest extends TestCase
{
    public function testFromArrayReturnsInstance(): void
    {
        $data = [
            'user_id' => 'foo',
            'depth'   => '512',
            'order'   => '3',
        ];

        $actual = Traversal::fromArray($data);
        self::assertSame('foo', $actual->nodeId);
        self::assertSame(512, $actual->depth);
        self::assertSame(3, $actual->order);
    }

    #[DataProvider('equalsProvider')]
    public function testEquals(Traversal $other, bool $expected): void
    {
        $traversal = new Traversal('foo', 512, 3);
        $actual    = $traversal->equals($other);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, list{Traversal, bool}>
     */
    public static function equalsProvider(): array
    {
        return [
            'equal'     => [new Traversal('foo', 512, 3), true],
            'not equal' => [new Traversal('bar', 512, 3), false],
        ];
    }
}
