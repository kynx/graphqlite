<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Exception;

use Kynx\GqLite\Exception\OutOfBoundsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OutOfBoundsException::class)]
final class OutOfBoundsExceptionTest extends TestCase
{
    public function testIndexOutOfBounds(): void
    {
        $expected = "Index 123 out of bounds";
        $actual   = OutOfBoundsException::indexOutOfBounds(123);
        self::assertSame($expected, $actual->getMessage());
    }
}
