<?php

/**
 * Shows how to use types to let static analysis know what `Graph::query()` returns
 *
 * phpcs:disable PSR1.Files.SideEffects.FoundWithSymbols
 * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * phpcs:disable Squiz.Classes.ClassFileName.NoMatch
 */

declare(strict_types=1);

use Kynx\GraphQLite\Cypher\Result;
use Kynx\GraphQLite\Graph;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;

require 'vendor/autoload.php';

/**
 * Declare our array shape
 *
 * @phpstan-type PersonArray = array{
 *     labels:     list<string>,
 *     properties: array{
 *         id:   string,
 *         name: string,
 *         age:  int
 *     }
 * }
 */
class StaticAnalysisExample
{
    private Graph $graph;

    public function __construct(string $extensionPath)
    {
        // Get a connection to an in-memory graph database
        $this->graph = Graph::connect($extensionPath, ':memory:');

        // Add some data
        $this->graph->nodes->upsert(new Node("alice", ["name" => "Alice", "age" => 30], "Person"));
        $this->graph->nodes->upsert(new Node("bob", ["name" => "Bob", "age" => 25], "Person"));
        $this->graph->edges->upsert(new Edge("alice", "bob", "KNOWS", ["since" => 2020]));
    }

    public function whoKnowsWho(): string
    {
        $knows = '';

        // Let static analysis know what the query returns
        /** @var Result<array{a: PersonArray, b: PersonArray}> $results */
        $results = $this->graph->query('MATCH (a:Person)-[:KNOWS]->(b) RETURN a, b');
        foreach ($results as $row) {
            $a = Node::fromArray($row['a']);
            $b = Node::fromArray($row['b']);

            $knows .= $a->properties['name'] . ' knows ' . $b->properties['name'] . "\n";
        }

        return $knows;
    }
}

$example = new StaticAnalysisExample((string) getenv('GRAPHQLITE_EXTENSION_PATH'));
echo $example->whoKnowsWho();

// Outputs:
// Alice knows Bob

// phpcs:enable
