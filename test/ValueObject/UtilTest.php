<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite\ValueObject;

use Kynx\GraphQLite\ValueObject\Util;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Util::class)]
final class UtilTest extends TestCase
{
    /**
     * @param array<string, mixed> $a
     * @param array<string, mixed> $b
     */
    #[DataProvider('propertiesAreEqualProvider')]
    public function testPropertiesAreEqual(array $a, array $b, bool $expected): void
    {
        $actual = Util::propertiesAreEqual($a, $b);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{array<string, mixed>, array<string, mixed>, bool}>
     */
    public static function propertiesAreEqualProvider(): array
    {
        $equal = ['a' => 'foo'];
        return [
            'empty'                 => [[], [], true],
            'strictly equal'        => [$equal, $equal, true],
            'different order'       => [['a' => 1, 'b' => 2], ['b' => 2, 'a' => 1], true],
            'nested equal'          => [['a' => ['b' => 1]], ['a' => ['b' => 1]], true],
            'different number'      => [['a' => 1, 'b' => 1], ['a' => 1], false],
            'different keys'        => [['a' => 1, 'b' => 2], ['a' => 1, 'c' => 2], false],
            'different value types' => [['a' => 1], ['a' => '1'], false],
            'different nested'      => [['a' => ['b' => 1]], ['a' => ['b' => 2]], false],
        ];
    }
}
