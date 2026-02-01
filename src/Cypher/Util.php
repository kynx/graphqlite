<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Cypher;

use Kynx\GraphQLite\Exception\InvalidArgumentException;

use function is_bool;
use function is_numeric;
use function is_scalar;
use function is_string;
use function preg_replace;
use function str_contains;
use function strtoupper;

final readonly class Util
{
    private const array ESCAPE = [
        "\\" => "\\\\",
        "'" => "\\'",
        '"' => '\\"',
        "\n" => ' ',
        "\r" => ' ',
        "\t" => ' ',
    ];

    private const array RESERVED = [
        # Clauses
        'CREATE', 'MATCH', 'RETURN', 'WHERE', 'DELETE', 'SET', 'REMOVE',
        'ORDER', 'BY', 'SKIP', 'LIMIT', 'WITH', 'UNWIND', 'AS', 'AND', 'OR',
        'NOT', 'IN', 'IS', 'NULL', 'TRUE', 'FALSE', 'MERGE', 'ON', 'CALL',
        'YIELD', 'DETACH', 'OPTIONAL', 'UNION', 'ALL', 'CASE', 'WHEN', 'THEN',
        'ELSE', 'END', 'EXISTS', 'FOREACH',
        # Aggregate functions
        'COUNT', 'SUM', 'AVG', 'MIN', 'MAX', 'COLLECT',
        # List functions and expressions
        'REDUCE', 'FILTER', 'EXTRACT', 'ANY', 'NONE', 'SINGLE',
        # Other reserved words
        'STARTS', 'ENDS', 'CONTAINS', 'XOR', 'DISTINCT', 'LOAD', 'CSV',
        'USING', 'PERIODIC', 'COMMIT', 'CONSTRAINT', 'INDEX', 'DROP', 'ASSERT',
    ];

    /** @psalm-suppress UnusedConstructor */
    private function __construct()
    {
    }

    /**
     * Returns escaped string for use in Cypher queries
     */
    public static function escape(string $value): string
    {
        return str_replace(array_keys(self::ESCAPE), self::ESCAPE, $value);
    }

    /**
     * Returns sanitized relation type for use with Cypher
     */
    public static function sanitizeRelationType(string $type): string
    {
        $safe = (string) preg_replace('/\W/', '_', $type);
        if (is_numeric(substr($safe, 0, 1))) {
            $safe = "REL_$safe";
        }
        if (in_array(strtoupper($safe), self::RESERVED)) {
            $safe = "REL_$safe";
        }

        return $safe;
    }

    /**
     * Returns properties as a Cypher property string
     *
     * @param array<array-key, mixed> $properties
     */
    public static function formatProperties(array $properties): string
    {
        $parts = [];
        foreach ($properties as $key => $value) {
            $parts[] = "$key: " . self::formatProperty($value);
        }

        return implode(', ', $parts);
    }

    public static function formatProperty(mixed $value): string
    {
        return match (true) {
            $value === null   => 'null',
            is_bool($value)   => $value ? 'true' : 'false',
            is_string($value) => "'" . self::escape($value). "'",
            is_scalar($value) => (string) $value,
            default           => throw InvalidArgumentException::notScalar($value),
        };
    }
}