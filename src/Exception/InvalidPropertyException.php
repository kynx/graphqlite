<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Exception;

use DomainException;

use function get_debug_type;
use function sprintf;

final class InvalidPropertyException extends DomainException implements ExceptionInterface
{
    public static function notScalar(mixed $value): self
    {
        return new self(sprintf('Expected scalar or null value, got %s', get_debug_type($value)));
    }

    public static function containsNullByte(): self
    {
        return new self('Property value cannot contain null bytes');
    }

    public static function propertiesContainsId(): self
    {
        return new self('Properties cannot contain an "id" key');
    }
}
