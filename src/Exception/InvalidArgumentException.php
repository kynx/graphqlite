<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Exception;

use Kynx\GraphQLite\Exception\ExceptionInterface;

final class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
    public static function notScalar(mixed $value): self
    {
        return new self(sprintf('Expected scalar value, got %s', get_debug_type($value)));
    }

    public static function dataContainsId(): self
    {
        return new self('Node data cannot contain an "id" key');
    }
}