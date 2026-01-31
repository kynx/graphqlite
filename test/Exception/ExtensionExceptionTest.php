<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite\Exception;

use Exception;
use Kynx\GraphQLite\Exception\ExtensionException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExtensionException::class)]
final class ExtensionExceptionTest extends TestCase
{
    public function testFailedToLoad(): void
    {
        $expected = "SQLLite failed to load '/path/to/extension.so'";
        $previous = new Exception('foo');

        $actual = ExtensionException::failedToLoad('/path/to/extension.so', $previous);
        self::assertSame($expected, $actual->getMessage());
        self::assertSame($previous, $actual->getPrevious());
    }

    public function testFailedToInitialise(): void
    {
        $expected = "GraphQLite extension failed to initialize: foo";
        $previous = new Exception('foo');

        $actual = ExtensionException::failedToInitialize('foo', $previous);
        self::assertSame($expected, $actual->getMessage());
        self::assertSame($previous, $actual->getPrevious());
    }
}
