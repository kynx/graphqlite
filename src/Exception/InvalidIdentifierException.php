<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Exception;

use DomainException;
use Throwable;

final class InvalidIdentifierException extends DomainException implements ExceptionInterface
{
    private function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function from(string $identifier): self
    {
        return new self("'$identifier' is not a valid Cypher identifier");
    }

    public static function reservedWord(string $identifier): self
    {
        return new self("'$identifier' is a Cypher reserved word");
    }

    public static function identifierRequired(string $type): self
    {
        return new self("'$type' identifier is required");
    }
}
