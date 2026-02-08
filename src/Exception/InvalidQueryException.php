<?php

declare(strict_types=1);

namespace Kynx\GqLite\Exception;

use PDOStatement;
use RuntimeException;
use Throwable;

use function implode;
use function sprintf;

final class InvalidQueryException extends RuntimeException implements ExceptionInterface
{
    public static function fromPdoException(PDOStatement $statement, ?Throwable $previous): self
    {
        return new self(sprintf(
            "Error executing statement: %s (%s %s)",
            $statement->queryString,
            (string) $statement->errorCode(),
            implode("; ", $statement->errorInfo())
        ), 0, $previous);
    }
}
