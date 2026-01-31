<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Exception;

use Kynx\GraphQLite\Exception\ExceptionInterface;
use RuntimeException;
use Throwable;

final class ExtensionException extends RuntimeException implements ExceptionInterface
{
    public static function failedToLoad(string $extensionPath, ?Throwable $previous = null): self
    {
        return new self("SQLLite failed to load '$extensionPath'", 0, $previous);
    }

    public static function failedToInitialize(string $result, ?Throwable $previous = null): self
    {
        return new self("GraphQLite extension failed to initialize: $result", 0, $previous);
    }
}
