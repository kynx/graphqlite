<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite\Cypher;

use Kynx\GraphQLite\Cypher\Util;
use Kynx\GraphQLite\Exception\InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(Util::class)]
final class UtilTest extends TestCase
{
    #[DataProvider('escapeProvider')]
    public function testEscape(string $value, string $expected): void
    {
        $actual = Util::escape($value);
        self::assertSame($expected, $actual);
    }

    public static function escapeProvider(): array
    {
        return [
            'single quotes' => ["It's", "It\\'s"],
            'double quotes' => ['Say "hi"', 'Say \\"hi\\"'],
            'backslash' => ['C:\\path', 'C:\\\\path'],
            'new lines' => ["line1\nline2", "line1 line2"],
            'carriage returns' => ["line1\rline2", "line1 line2"],
            'tabs' => ["col1\tcol2", "col1 col2"],
            'no escape' => [" foo foo foo ", " foo foo foo "],
        ];
    }

    #[DataProvider('relationTypeProvider')]
    public function testSanitizeRelationType(string $type, string $expected): void
    {
        $actual = Util::sanitizeRelationType($type);
        self::assertSame($expected, $actual);
    }

    public static function relationTypeProvider(): array
    {
        return [
            'sane' => ['KNOWS', 'KNOWS'],
            'special chars' => ['RELATED-TO', 'RELATED_TO'],
            'leading digit' => ['123_TYPE', 'REL_123_TYPE'],
            'reserved word' => ['CREATE', 'REL_CREATE'],
        ];
    }

    /** @noinspection PhpParamsInspection */
    public function testFormatPropertiesWithNonScalarThrowsException(): void
    {
        $properties = ['foo' => new stdClass()];

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage('Expected scalar value, got stdClass');
        Util::formatProperties($properties);
    }

    #[DataProvider('propertiesProvider')]
    public function testFormatProperties(array $properties, string $expected): void
    {
        $actual = Util::formatProperties($properties);
        self::assertSame($expected, $actual);
    }

    public static function propertiesProvider(): array
    {
        return [
            'string' => [['key' => "foo\nbar"], "key: 'foo bar'"],
            'boolean' => [['key' => true], 'key: true'],
            'null' => [['key' => null], 'key: null'],
            'integer' => [['key' => 123], 'key: 123'],
            'multiple' => [['key1' => 'foo', 'key2' => 123], "key1: 'foo', key2: 123"],
        ];
    }
}
