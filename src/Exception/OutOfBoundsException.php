<?php

declare(strict_types=1);

namespace Kynx\GqLite\Exception;

final class OutOfBoundsException extends \OutOfBoundsException implements ExceptionInterface
{
    public static function indexOutOfBounds(int $position): self
    {
        return new self("Index $position out of bounds");
    }
}
