<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

interface ConnectionInterface
{
    public function cypher(string $query, array $params = []): CypherResult;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
