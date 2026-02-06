<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use JsonException;
use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\Exception\ExtensionException;
use Kynx\GraphQLite\Exception\InvalidQueryException;
use Kynx\GraphQLite\Exception\InvalidResultException;
use PDO;
use Pdo\Sqlite;
use PDOException;

use function array_is_list;
use function array_keys;
use function assert;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function str_contains;
use function str_starts_with;

use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;

final readonly class Connection implements ConnectionInterface
{
    private const int JSON_DECODE_FLAGS = JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING;

    private function __construct(private Sqlite $connection, string $extensionPath)
    {
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->loadExtension($extensionPath);
    }

    public static function connect(string $extensionPath, string $database = self::MEMORY): self
    {
        return self::wrap($extensionPath, new Sqlite("sqlite:$database"));
    }

    public static function wrap(string $extensionPath, Sqlite $connection): self
    {
        return new self($connection, $extensionPath);
    }

    public function cypher(string $query, array $params = []): Result
    {
        if ($params !== []) {
            $statement       = $this->connection->prepare('SELECT cypher(:query, :json)');
            $statementParams = [':query' => $query, ':params' => json_encode($params)];
        } else {
            $statement       = $this->connection->prepare('SELECT cypher(:query)');
            $statementParams = [':query' => $query];
        }

        try {
            $statement->execute($statementParams);
        } catch (PDOException $exception) {
            throw InvalidQueryException::fromPdoException($statement, $exception);
        }

        /** @var mixed $json */
        $json = $statement->fetchColumn() ?? false;
        if ($json === false) {
            return new Result([], []);
        }

        if (! is_string($json) || str_starts_with($json, 'Query executed')) {
            return new Result([], []);
        }

        try {
            /** @var array<string, mixed>|list<array<string, mixed>>|null|false $data */
            $data = json_decode((string) $json, true, 512, self::JSON_DECODE_FLAGS);
        } catch (JsonException $exception) {
            throw InvalidResultException::fromInvalidJson($exception);
        }

        if ($data === null) {
            return new Result([], []);
        }

        if (! is_array($data)) {
            return new Result([['result' => $data]], ['result']);
        }

        if ($data === []) {
            return new Result([], []);
        }

        if (array_is_list($data)) {
            $first = $data[0];
            if ($first === []) {
                return new Result([], []);
            }
            if (is_array($first) && ! array_is_list($first)) {
                return new Result($data, array_keys($first));
            }

            // List of scalars - this happens when C returns raw JSON array
            // for single-cell queries (e.g., range(), tail(), graph algorithms)
            // Treat as single row with the original JSON string as value
            return new Result([['result' => $json]], ['result']);
        }

        return new Result([$data], array_keys($data));
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollback(): void
    {
        $this->connection->rollBack();
    }

    private function loadExtension(string $extensionPath): void
    {
        try {
            $this->connection->loadExtension($extensionPath);
        } catch (PDOException $exception) {
            throw ExtensionException::failedToLoad($extensionPath, $exception);
        }

        try {
            $statement = $this->connection->query('SELECT graphqlite_test()', PDO::FETCH_NUM);
            assert($statement !== false);
        } catch (PDOException $exception) {
            throw ExtensionException::failedToInitialize($exception->getMessage(), $exception);
        }

        $result = (string) $statement->fetchColumn();
        if (! str_contains($result, 'successfully')) {
            throw ExtensionException::failedToInitialize($result);
        }
    }
}
