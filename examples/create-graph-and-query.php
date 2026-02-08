<?php

/**
 * Basic example of creating a graph and querying it
 */

declare(strict_types=1);

use Kynx\GraphQLite\Graph;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;

require 'vendor/autoload.php';

// replace with path to GraphQLite extension installed above
$extensionPath = (string) getenv('GRAPHQLITE_EXTENSION_PATH');

// Get a connection to an in-memory graph database
$graph = Graph::connect($extensionPath, ':memory:');

// Add some data
$graph->nodes->upsert(new Node("alice", ["name" => "Alice", "age" => 30], "Person"));
$graph->nodes->upsert(new Node("bob", ["name" => "Bob", "age" => 25], "Person"));
$graph->edges->upsert(new Edge("alice", "bob", "KNOWS", ["since" => 2020]));

// Query with Cypher
$results = $graph->query('MATCH (a:Person)-[:KNOWS]->(b) RETURN a.name AS a, b.name AS b');
foreach ($results as $row) {
    echo $row['a'] . ' knows ' . $row['b'] . "\n";
}
