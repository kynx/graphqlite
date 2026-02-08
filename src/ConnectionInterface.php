<?php

declare(strict_types=1);

namespace Kynx\GqLite;

use Kynx\GqLite\Cypher\Result;

interface ConnectionInterface
{
    public const string MEMORY = ':memory:';

    /**
     * @param array<string, null|scalar> $params
     * @return Result<covariant array<array-key, mixed>>
     */
    public function cypher(string $query, array $params = []): Result;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
