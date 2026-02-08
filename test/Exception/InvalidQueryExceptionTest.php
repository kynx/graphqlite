<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Exception;

use Exception;
use Kynx\GqLite\Exception\InvalidQueryException;
use PDOStatement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidQueryException::class)]
final class InvalidQueryExceptionTest extends TestCase
{
    public function testFromPdoException(): void
    {
        $expected               = "Error executing statement: SELECT cypher(FOO) (HY000 Error Info)";
        $statement              = self::createStub(PDOStatement::class);
        $statement->queryString = "SELECT cypher(FOO)";
        $statement->method('errorCode')
            ->willReturn('HY000');
        $statement->method('errorInfo')
            ->willReturn(['Error Info']);
        $previous = new Exception('foo');

        $actual = InvalidQueryException::fromPdoException($statement, $previous);
        self::assertSame($expected, $actual->getMessage());
        self::assertSame($previous, $actual->getPrevious());
    }
}
