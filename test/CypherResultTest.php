<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite;

use Kynx\GraphQLite\CypherResult;
use Kynx\GraphQLite\Exception\OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CypherResult::class)]
final class CypherResultTest extends TestCase
{
    public function testGetColumnsReturnsColumns(): void
    {
        $expected     = ['foo', 'bar'];
        $cypherResult = new CypherResult([], $expected);

        $actual = $cypherResult->getColumns();
        self::assertSame($expected, $actual);
    }

    public function testCurrentThrowsExceptionWhenOutOfBounds(): void
    {
        $cypherResult = new CypherResult([], []);

        self::expectException(OutOfBoundsException::class);
        $cypherResult->current();
    }

    public function testResultIsIterable(): void
    {
        $rows         = [['foo' => 'bar'], ['foo' => 'baz']];
        $cypherResult = new CypherResult($rows, ['foo']);

        foreach ($cypherResult as $i => $row) {
            self::assertSame($rows[$i], $row);
        }
    }

    public function testResultIsSeekable(): void
    {
        $rows         = [['foo' => 'bar'], ['foo' => 'baz']];
        $cypherResult = new CypherResult($rows, ['foo']);

        $cypherResult->next();
        $cypherResult->seek(0);
        self::assertSame($rows[0], $cypherResult->current());

        $cypherResult->seek(1);
        self::assertSame($rows[1], $cypherResult->current());

        self::expectException(OutOfBoundsException::class);
        $cypherResult->seek(2);
    }

    public function testCount(): void
    {
        $rows         = [['foo' => 'bar'], ['foo' => 'baz']];
        $cypherResult = new CypherResult($rows, ['foo']);

        $actual = $cypherResult->count();
        self::assertSame(2, $actual);
    }
}
