<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Exception;

use JsonException;
use RuntimeException;

use function sprintf;

final class InvalidResultException extends RuntimeException implements ExceptionInterface
{
    public static function fromInvalidJson(JsonException $exception): self
    {
        return new self(sprintf(
            'Invalid JSON result from Cypher query: %s',
            $exception->getMessage()
        ), 0, $exception);
    }
}
