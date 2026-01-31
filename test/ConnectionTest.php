<?php

declare(strict_types=1);

namespace KynxTest\GraphQLite;

use Kynx\GraphQLite\Connection;
use Kynx\GraphQLite\Exception\ExtensionException;
use Kynx\GraphQLite\Exception\InvalidQueryException;
use Pdo\Sqlite;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_map;
use function file_exists;
use function getenv;
use function iterator_to_array;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

#[CoversClass(Connection::class)]
final class ConnectionTest extends TestCase
{
    private string $extensionPath = '';
    private ?string $databasePath = null;

    protected function setUp(): void
    {
        parent::setUp();

        $envVar = (string) getenv('GRAPHQLITE_EXTENSION_PATH');
        if ($envVar === '') {
            self::fail("GRAPHQLITE_EXTENSION_PATH environment variable not set");
        }

        $this->extensionPath = $envVar;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->databasePath !== null && file_exists($this->databasePath)) {
            unlink($this->databasePath);
        }
    }

    public function testConnectWithInvalidExtensionPathThrowsException(): void
    {
        $extensionPath = __DIR__ . '/non-existing-extension.so';
        self::expectException(ExtensionException::class);
        self::expectExceptionMessage("SQLLite failed to load '$extensionPath'");
        Connection::connect(':memory:', $extensionPath);
    }

    public function testConnectConnectsWithDefaultConfiguration(): void
    {
        $expected   = ['n' => 1];
        $connection = $this->getConnection();
        $result     = $connection->cypher("RETURN 1 AS n");
        $actual     = $result->current();
        self::assertSame($expected, $actual);
    }

    public function testWrapWrapsExistingConnection(): void
    {
        $expected   = ['n' => 1];
        $existing   = new Sqlite('sqlite:memory:');
        $connection = Connection::wrap($existing, $this->extensionPath);
        $result     = $connection->cypher("RETURN 1 AS n");
        $actual     = $result->current();
        self::assertSame($expected, $actual);
    }

    public function testConnectConnectsToDatabaseFile(): void
    {
        $expected   = ['n' => 1];
        $path       = $this->getDatabasePath();
        $connection = Connection::connect("$path", $this->extensionPath);
        $result     = $connection->cypher("RETURN 1 AS n");
        $actual     = $result->current();
        self::assertSame($expected, $actual);
    }

    public function testCreateNode(): void
    {
        $expected   = [
            ['n.name' => 'Alice', 'n.age' => 30],
        ];
        $connection = $this->getConnection();
        $connection->cypher("CREATE (n:Person {name: 'Alice', age: 30})");

        $results = $connection->cypher("MATCH (n:Person) RETURN n.name, n.age");
        $actual  = iterator_to_array($results);
        self::assertSame($expected, $actual);
    }

    public function testCreateRelationShip(): void
    {
        $expected   = [
            ['a.name' => 'Alice', 'b.name' => 'Bob'],
        ];
        $connection = $this->getConnection();
        $connection->cypher("CREATE (a:Person {name: 'Alice'})");
        $connection->cypher("CREATE (b:Person {name: 'Bob'})");
        $connection->cypher(
            "
            MATCH (a:Person {name: 'Alice'}), (b:Person {name: 'Bob'})
            CREATE (a)-[:KNOWS]->(b)
        "
        );

        $result = $connection->cypher("MATCH (a)-[:KNOWS]->(b) RETURN a.name, b.name");
        $actual = iterator_to_array($result);
        self::assertSame($expected, $actual);
    }

    public function testReturnScalar(): void
    {
        $expected   = [
            ['num' => 42, 'str' => 'hello'],
        ];
        $connection = $this->getConnection();

        $connection->cypher("RETURN 42 as num, 'hello' as str");
        $results = $connection->cypher("RETURN 42 as num, 'hello' as str");
        $actual  = iterator_to_array($results);
        self::assertSame($expected, $actual);
    }

    public function testReturnMultipleRows(): void
    {
        $expected   = [1, 2, 3];
        $connection = $this->getConnection();
        $connection->cypher("CREATE (n:Num {val: 1})");
        $connection->cypher("CREATE (n:Num {val: 2})");
        $connection->cypher("CREATE (n:Num {val: 3})");

        $result = $connection->cypher("MATCH (n:Num) RETURN n.val ORDER BY n.val");
        $actual = array_map(static fn(array $row): mixed => $row['n.val'], iterator_to_array($result));
        self::assertSame($expected, $actual);
    }

    public function testAggregation(): void
    {
        $expected   = [
            ['cnt' => 3, 'total' => 60],
        ];
        $connection = $this->getConnection();
        $connection->cypher("CREATE (n:Num {val: 10})");
        $connection->cypher("CREATE (n:Num {val: 20})");
        $connection->cypher("CREATE (n:Num {val: 30})");

        $result = $connection->cypher("MATCH (n:Num) RETURN count(n) as cnt, sum(n.val) as total");
        $actual = iterator_to_array($result);
        self::assertSame($expected, $actual);
    }

    public function testEmptyResult(): void
    {
        $connection = $this->getConnection();

        $results = $connection->cypher("MATCH (n:NonExistent) RETURN n");
        self::assertFalse($results->valid());
    }

    public function testCommit(): void
    {
        $expected = [
            ['n.name' => 'Alice', 'n.age' => 30],
        ];
        $path     = $this->getDatabasePath();
        $conn1    = Connection::connect("$path", $this->extensionPath);
        $conn2    = Connection::connect("$path", $this->extensionPath);
        $conn1->beginTransaction();
        $conn1->cypher("CREATE (n:Person {name: 'Alice', age: 30})");
        $conn1->commit();

        $results = $conn2->cypher("MATCH (n:Person) RETURN n.name, n.age");
        $actual  = iterator_to_array($results);
        self::assertSame($expected, $actual);
    }

    public function testRollback(): void
    {
        $path  = $this->getDatabasePath();
        $conn1 = Connection::connect("$path", $this->extensionPath);
        $conn2 = Connection::connect("$path", $this->extensionPath);
        $conn1->beginTransaction();
        $conn1->cypher("CREATE (n:Person {name: 'Alice', age: 30})");
        $conn1->rollback();

        $results = $conn2->cypher("MATCH (n:Person) RETURN n.name, n.age");
        self::assertCount(0, $results);
    }

    public function testInvalidQueryThrowsException(): void
    {
        $connection = $this->getConnection();

        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage("Error executing statement");
        $connection->cypher("FOO (n:NonExistent) RETURN n");
    }

    private function getConnection(): Connection
    {
        return Connection::connect(":memory:", $this->extensionPath);
    }

    private function getDatabasePath(): string
    {
        $this->databasePath ??= (string) tempnam(sys_get_temp_dir(), 'graphqlite_test');
        return $this->databasePath;
    }
}
