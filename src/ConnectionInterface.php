<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Kynx\GraphQLite\Cypher\Result;

interface ConnectionInterface
{
    public function cypher(string $query, array $params = []): Result;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
