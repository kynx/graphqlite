<?php

declare(strict_types=1);

namespace Kynx\GqLite\Cypher;

use Kynx\GqLite\Exception\InvalidIdentifierException;
use Kynx\GqLite\Exception\InvalidPropertyException;

use function addslashes;
use function get_debug_type;
use function implode;
use function in_array;
use function is_bool;
use function is_numeric;
use function is_scalar;
use function is_string;
use function preg_match;
use function str_contains;
use function str_ends_with;
use function str_starts_with;
use function strtoupper;
use function substr;
use function trim;

final readonly class CypherUtil
{
    private const array RESERVED = [
        // Clauses
        'CREATE',
        'MATCH',
        'RETURN',
        'WHERE',
        'DELETE',
        'SET',
        'REMOVE',
        'ORDER',
        'BY',
        'SKIP',
        'LIMIT',
        'WITH',
        'UNWIND',
        'AS',
        'AND',
        'OR',
        'NOT',
        'IN',
        'IS',
        'NULL',
        'TRUE',
        'FALSE',
        'MERGE',
        'ON',
        'CALL',
        'YIELD',
        'DETACH',
        'OPTIONAL',
        'UNION',
        'ALL',
        'CASE',
        'WHEN',
        'THEN',
        'ELSE',
        'END',
        'EXISTS',
        'FOREACH',
        // Aggregate functions
        'COUNT',
        'SUM',
        'AVG',
        'MIN',
        'MAX',
        'COLLECT',
        // List functions and expressions
        'REDUCE',
        'FILTER',
        'EXTRACT',
        'ANY',
        'NONE',
        'SINGLE',
        // Other reserved words
        'STARTS',
        'ENDS',
        'CONTAINS',
        'XOR',
        'DISTINCT',
        'LOAD',
        'CSV',
        'USING',
        'PERIODIC',
        'COMMIT',
        'CONSTRAINT',
        'INDEX',
        'DROP',
        'ASSERT',
    ];

    /** @psalm-suppress UnusedConstructor */
    private function __construct()
    {
    }

    /**
     * @see https://neo4j.com/docs/cypher-manual/current/syntax/naming/
     *
     * @phpstan-assert non-empty-string $identifier
     */
    public static function validateIdentifier(mixed $identifier): void
    {
        if (! is_string($identifier)) {
            throw InvalidIdentifierException::from(get_debug_type($identifier));
        }

        $trimmed = trim($identifier);
        if ($trimmed === '') {
            throw InvalidIdentifierException::from($identifier);
        }

        if (str_starts_with($trimmed, '`') && str_ends_with($trimmed, '`')) {
            return;
        }

        if (in_array(strtoupper($trimmed), self::RESERVED, true)) {
            throw InvalidIdentifierException::reservedWord($identifier);
        }

        $first = substr($trimmed, 0, 1);
        if (is_numeric($first)) {
            throw InvalidIdentifierException::from($identifier);
        }

        if (preg_match('/\W/', $trimmed)) {
            throw InvalidIdentifierException::from($identifier);
        }
    }

    /**
     * @param array<array-key, mixed> $properties
     * @phpstan-assert array<string, null|scalar> $properties
     */
    public static function validateProperties(array $properties): void
    {
        foreach ($properties as $key => $value) {
            self::validateIdentifier($key);
            if (! (is_scalar($value) || $value === null)) {
                throw InvalidPropertyException::notScalar($value);
            }
            if (is_string($value) && str_contains($value, "\0")) {
                throw InvalidPropertyException::containsNullByte();
            }
        }
    }

    /**
     * @param array<mixed> $labels
     * @phpstan-assert non-empty-array<non-empty-string> $labels
     */
    public static function validateLabels(array $labels): void
    {
        if ($labels === []) {
            throw InvalidIdentifierException::identifierRequired('label');
        }

        foreach ($labels as $label) {
            self::validateIdentifier($label);
        }
    }

    /**
     * Returns properties as a Cypher property string
     *
     * @param array<array-key, mixed> $properties
     */
    public static function formatProperties(array $properties): string
    {
        self::validateProperties($properties);

        $parts = [];
        foreach ($properties as $key => $value) {
            $parts[] = "$key: " . self::formatProperty($value);
        }

        return '{' . implode(', ', $parts) . '}';
    }

    public static function formatProperty(mixed $value): string
    {
        return match (true) {
            $value === null   => 'null',
            is_bool($value)   => $value ? 'true' : 'false',
            is_string($value) => "'" . self::escape($value) . "'",
            is_scalar($value) => (string) $value,
            default           => throw InvalidPropertyException::notScalar($value),
        };
    }

    /**
     * Returns escaped string for use in Cypher queries
     */
    public static function escape(string $value): string
    {
        return addslashes($value);
    }
}
