<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Cypher;

use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\Exception\OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Result::class)]
final class ResultTest extends TestCase
{
    public function testGetColumnsReturnsColumns(): void
    {
        $expected     = ['foo', 'bar'];
        $cypherResult = new Result([], $expected);

        $actual = $cypherResult->getColumns();
        self::assertSame($expected, $actual);
    }

    public function testCurrentThrowsExceptionWhenOutOfBounds(): void
    {
        $cypherResult = new Result([], []);

        self::expectException(OutOfBoundsException::class);
        $cypherResult->current();
    }

    public function testResultIsIterable(): void
    {
        $rows         = [['foo' => 'bar'], ['foo' => 'baz']];
        $cypherResult = new Result($rows, ['foo']);

        foreach ($cypherResult as $i => $row) {
            self::assertSame($rows[$i], $row);
        }
    }

    public function testResultIsSeekable(): void
    {
        $rows         = [['foo' => 'bar'], ['foo' => 'baz']];
        $cypherResult = new Result($rows, ['foo']);

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
        $cypherResult = new Result($rows, ['foo']);

        $actual = $cypherResult->count();
        self::assertSame(2, $actual);
    }
}
