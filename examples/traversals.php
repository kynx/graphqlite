<?php

declare(strict_types=1);

use Kynx\GqLite\Graph;
use Kynx\GqLite\ValueObject\Edge;
use Kynx\GqLite\ValueObject\Node;

require 'vendor/autoload.php';

/**
 * Create a test graph with the following structure
 *         A
 *        / \
 *       B   D
 *      /
 *     C
 */

// replace with path to GraphQLite extension
$extensionPath = (string) getenv('GRAPHQLITE_EXTENSION_PATH');

// Get a connection to an in-memory graph database
$graph = Graph::connect($extensionPath, ':memory:');

$graph->nodes->upsert(new Node('A', [], 'Test'));
$graph->nodes->upsert(new Node('B', [], 'Test'));
$graph->nodes->upsert(new Node('C', [], 'Test'));
$graph->nodes->upsert(new Node('D', [], 'Test'));
$graph->edges->upsert(new Edge('A', 'B'));
$graph->edges->upsert(new Edge('B', 'C'));
$graph->edges->upsert(new Edge('A', 'D'));

echo "Breadth first:\n";
foreach ($graph->traversals->breadthFirst('A') as $traversal) {
    echo $traversal->nodeId . PHP_EOL;
}

// Outputs
// Breadth first:
// A
// B
// D
// C

echo "Depth first:\n";
foreach ($graph->traversals->depthFirst('A') as $traversal) {
    echo $traversal->nodeId . PHP_EOL;
}

// Outputs
// Depth first:
// A
// B
// C
// D
