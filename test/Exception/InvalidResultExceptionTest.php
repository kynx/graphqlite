<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Exception;

use JsonException;
use Kynx\GqLite\Exception\InvalidResultException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidResultException::class)]
final class InvalidResultExceptionTest extends TestCase
{
    public function testFromInvalidJson(): void
    {
        $expected  = 'Invalid JSON result from Cypher query: foo';
        $exception = new JsonException('foo');

        $actual = InvalidResultException::fromInvalidJson($exception);
        self::assertSame($expected, $actual->getMessage());
        self::assertSame($exception, $actual->getPrevious());
    }
}
