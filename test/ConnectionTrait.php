<?php

declare(strict_types=1);

namespace KynxTest\GqLite;

use Kynx\GqLite\Connection;
use PHPUnit\Framework\TestCase;

use function getenv;

/**
 * @psalm-require-extends TestCase
 */
trait ConnectionTrait
{
    protected function getExtensionPath(): string
    {
        $extensionPath = (string) getenv('GRAPHQLITE_EXTENSION_PATH');
        if ($extensionPath === '') {
            self::fail("GRAPHQLITE_EXTENSION_PATH environment variable not set");
        }

        return $extensionPath;
    }

    protected function getConnection(): Connection
    {
        return Connection::connect($this->getExtensionPath());
    }
}
