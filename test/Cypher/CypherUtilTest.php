<?php

declare(strict_types=1);

namespace KynxTest\GqLite\Cypher;

use Exception;
use Kynx\GqLite\Cypher\CypherUtil;
use Kynx\GqLite\Exception\ExceptionInterface;
use Kynx\GqLite\Exception\InvalidIdentifierException;
use Kynx\GqLite\Exception\InvalidPropertyException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(CypherUtil::class)]
final class CypherUtilTest extends TestCase
{
    #[DataProvider('invalidIdentifierProvider')]
    public function testValidateIdentifierThrowsException(mixed $identifier, string $expected): void
    {
        self::expectException(InvalidIdentifierException::class);
        self::expectExceptionMessage($expected);

        CypherUtil::validateIdentifier($identifier);
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function invalidIdentifierProvider(): array
    {
        return [
            'not string'    => [new stdClass(), "'stdClass' is not a valid Cypher identifier"],
            'empty'         => ['', "'' is not a valid Cypher identifier"],
            'space'         => [' ', "' ' is not a valid Cypher identifier"],
            'reserved'      => ['match', "'match' is a Cypher reserved word"],
            'numeric start' => ['1abc', "'1abc' is not a valid Cypher identifier"],
            'non-word'      => ['foo?bar', "'foo?bar' is not a valid Cypher identifier"],
            'backticks'     => ['f`oo`bar', "'f`oo`bar' is not a valid Cypher identifier"],
        ];
    }

    public function testValidateIdentifierWithBackticksDoesNotThrowException(): void
    {
        $identifier = " `1abc` ";
        try {
            /** @phpstan-ignore staticMethod.alreadyNarrowedType (Validation does more than type-narrowing!) */
            CypherUtil::validateIdentifier($identifier);
            self::expectNotToPerformAssertions();
        } catch (InvalidIdentifierException $e) {
            self::fail("Expected no exception, got: {$e->getMessage()}");
        }
    }

    /**
     * @param array<string, mixed> $properties
     * @param class-string<Exception> $exception
     */
    #[DataProvider('invalidPropertyProvider')]
    public function testValidatePropertiesThrowsException(array $properties, string $exception): void
    {
        self::expectException($exception);

        CypherUtil::validateProperties($properties);
    }

    /**
     * @return array<string, array{array<string, mixed>, class-string<Exception>}>
     */
    public static function invalidPropertyProvider(): array
    {
        return [
            'invalid identifier' => [['1abc' => 'value'], InvalidIdentifierException::class],
            'not scalar'         => [['foo' => ['bar']], InvalidPropertyException::class],
            'null byte'          => [['foo' => "a\0b"], InvalidPropertyException::class],
        ];
    }

    /**
     * @param array<string, mixed> $properties
     */
    #[DataProvider('validPropertyProvider')]
    public function testValidatePropertiesDoesNotThrowException(array $properties): void
    {
        try {
            CypherUtil::validateProperties($properties);
            self::expectNotToPerformAssertions();
        } catch (ExceptionInterface $e) {
            self::fail("Expected no exception, got: {$e->getMessage()}");
        }
    }

    /**
     * @return array<string, array<array<string, mixed>>>
     */
    public static function validPropertyProvider(): array
    {
        return [
            'null'       => [['key' => null]],
            'string'     => [['key' => 'value']],
            'line break' => [['key' => "a\nb"]],
        ];
    }

    public function testFormatPropertiesValidatesKeys(): void
    {
        self::expectException(InvalidIdentifierException::class);

        CypherUtil::formatProperties(['1abc' => 'value']);
    }

    public function testFormatPropertiesWithNonScalarThrowsException(): void
    {
        $properties = ['foo' => new stdClass()];
        self::expectException(InvalidPropertyException::class);
        self::expectExceptionMessage('Expected scalar or null value, got stdClass');

        CypherUtil::formatProperties($properties);
    }

    /**
     * @param array<string, mixed> $properties
     */
    #[DataProvider('propertiesProvider')]
    public function testFormatProperties(array $properties, string $expected): void
    {
        $actual = CypherUtil::formatProperties($properties);
        self::assertSame($expected, $actual);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function propertiesProvider(): array
    {
        return [
            'string'        => [['key' => " foo bar "], "{key: ' foo bar '}"],
            'boolean'       => [['key' => true], '{key: true}'],
            'null'          => [['key' => null], '{key: null}'],
            'integer'       => [['key' => 123], '{key: 123}'],
            'multiple'      => [['key1' => 'foo', 'key2' => 123], "{key1: 'foo', key2: 123}"],
            'single quotes' => [['key' => "It's"], "{key: 'It\\'s'}"],
            'double quotes' => [['key' => 'Say "hi"'], '{key: \'Say \\"hi\\"\'}'],
            'backslash'     => [['key' => 'C:\\path'], "{key: 'C:\\\\path'}"],
        ];
    }
}
