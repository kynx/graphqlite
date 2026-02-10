<?php

declare(strict_types=1);

namespace KynxTest\GqLite;

use Kynx\GqLite\Connection;
use Kynx\GqLite\Exception\InvalidQueryException;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Tests to figure out how closely GraphQLite's implementation follows the Cypher documentation
 */
#[CoversNothing]
final class CypherImplementationTest extends TestCase
{
    use ConnectionTrait;

    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->getConnection();
    }

    /**
     * Tabs, line feeds and new lines are replaced with spaces by the python binding, but it looks like they work...
     */
    public function testTabsInProperties(): void
    {
        $expected = ['id' => 'test', 'foo' => "a\tb"];
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: 'a\tb'})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('properties', $row);
        self::assertSame($expected, $row['properties']);
    }

    public function testLineFeedsInProperties(): void
    {
        $expected = ['id' => 'test', 'foo' => "a\rb"];
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: 'a\rb'})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('properties', $row);
        self::assertSame($expected, $row['properties']);
    }

    public function testNewLinesInProperties(): void
    {
        $expected = ['id' => 'test', 'foo' => "a\nb"];
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: 'a\nb'})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('properties', $row);
        self::assertSame($expected, $row['properties']);
    }

    public function testNullBytesInProperties(): void
    {
        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage('unexpected end of file');

        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: 'a\0b'})");
    }

    public function testNullPropertiesAreNotReturned(): void
    {
        $expected = ['id' => 'test'];
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: null})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('properties', $row);
        self::assertSame($expected, $row['properties']);
    }

    public function testListInProperties(): void
    {
        $expected = ['a', 'b'];
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: ['a', 'b']})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('properties', $row);
        self::assertIsArray($row['properties']);
        self::assertArrayHasKey('foo', $row['properties']);
        self::assertSame($expected, $row['properties']['foo']);
    }

    /**
     * Dates are not saved either
     */
    public function testDateInProperties(): void
    {
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: date('2025-02-07')})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('properties', $row);
        self::assertIsArray($row['properties']);
        self::assertArrayNotHasKey('foo', $row['properties']);
    }

    public function testPropertiesWithInvalidIdentifier(): void
    {
        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage('unexpected INTEGER');

        $this->connection->cypher("CREATE (n:Test {id: 'test', 1abc: 'foo'})");
    }

    /**
     * Can't use parameter for properties - gotta stick with our manual escaping stuff :(
     */
    public function testCreatePropertiesWithParameter(): void
    {
        $properties = ['id' => 'test', 'foo' => 'bar'];
        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage('unexpected PARAMETER');

        // @phpstan-ignore argument.type
        $this->connection->cypher("CREATE (n:Test \$properties)", ['properties' => $properties]);
    }

    public function testInvalidLabel(): void
    {
        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage('unexpected INTEGER');

        $this->connection->cypher("CREATE (n:1abc {id: 'test'})");
    }

    public function testLabelEscapedWithBackticks(): void
    {
        $expected = ['1abc'];
        $this->connection->cypher("CREATE (n:`1abc` {id: 'test'})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('labels', $row);
        self::assertSame($expected, $row['labels']);
    }

    /**
     * Multiple labels might be saved, but they're not returned :(
     */
    public function testMultipleLabels(): void
    {
        $expected = ['A', 'B', 'C'];
        $this->connection->cypher("CREATE (n:A:B:C {id: 'test'})");

        $result = $this->connection->cypher("MATCH (n {id: 'test'}) RETURN n");
        $row    = $result->current()['n'];
        self::assertIsArray($row);
        self::assertArrayHasKey('labels', $row);
        self::assertSame($expected, $row['labels']);
    }

    /**
     * Ampersand separators for labels are not supported
     */
    public function testAmpersandLabels(): void
    {
        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage('invalid token');

        $this->connection->cypher("CREATE (n:A&B&C {id: 'test'})");
    }

    /**
     * Properties must be set individually :(
     */
    public function testSetAllProperties(): void
    {
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: 'bar'})");
        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage('syntax error');

        $this->connection->cypher("MATCH (n {id: 'test'}) SET n {id: 'test', bar: 'baz'}");
    }

    /**
     * Doesn't work with parameterised query either :(
     */
    public function testSetAllPropertiesWithParameter(): void
    {
        $properties = ['id' => 'test', 'bar' => 'baz'];
        $this->connection->cypher("CREATE (n:Test {id: 'test', foo: 'bar'})");
        self::expectException(InvalidQueryException::class);
        self::expectExceptionMessage('syntax error');

        // @phpstan-ignore argument.type
        $this->connection->cypher("MATCH (n {id: 'test'}) SET n \$properties", ['properties' => $properties]);
    }
}
