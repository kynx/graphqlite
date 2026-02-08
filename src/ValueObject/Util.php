<?php

declare(strict_types=1);

namespace Kynx\GqLite\ValueObject;

use function array_keys;
use function count;
use function is_array;
use function ksort;

final readonly class Util
{
    /** @psalm-suppress UnusedConstructor */
    private function __construct()
    {
    }

    /**
     * Returns true if the properties are (mostly) strictly equal
     *
     * Arrays are considered equal if they have the same keys and the associated values
     * are strictly equal - (ie stricter than just `[1] == ['1']`)
     *
     * @param array<string, mixed> $a
     * @param array<string, mixed> $b
     */
    public static function propertiesAreEqual(array $a, array $b): bool
    {
        if ($a === $b) {
            return true;
        }
        if (count($a) !== count($b)) {
            return false;
        }

        ksort($a);
        ksort($b);
        if (array_keys($a) !== array_keys($b)) {
            return false;
        }

        foreach ($a as $key => $value) {
            if (is_array($value) && is_array($b[$key])) {
                if (! self::propertiesAreEqual($value, $b[$key])) {
                    return false;
                }
            } elseif ($value !== $b[$key]) {
                return false;
            }
        }

        return true;
    }
}
