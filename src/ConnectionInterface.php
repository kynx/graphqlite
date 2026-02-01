<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

interface ConnectionInterface
{
    /**
     * @param array<string, null|scalar> $params
     */
    public function cypher(string $query, array $params = []): CypherResult;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
