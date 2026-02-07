# kynx/graphqlite

[![Continuous Integration](https://github.com/kynx/graphqlite/actions/workflows/continuous-integration.yml/badge.svg?branch=main)](https://github.com/kynx/graphqlite/actions/workflows/continuous-integration.yml)

Talk to an embedded graph database with PHP.

> This project is in the early stages of development! Feel free to play, but bear in mind that there are a lot of
> missing features!

This package provides a driver for the [SQLite]-based graph database [GraphQLite]. If you came here looking for
something to handle the [GraphQL protocol], you're in the wrong place: try [Packagist]!

## Installation

```bash
composer require kynx/graphqlite
```

GraphQLite provides an SQLLite extension that must be available locally. You will need to ensure that is installed:

```bash
brew install graphqlite       # macOS/Linux (Homebrew)
pip install graphqlite        # Python
```

Make a note of the path it's installed `graphqlite.(dylib|so|dll)` to - you will need this later.

## Usage

GraphQList uses the [Cypher] language:

```php
use Kynx\GraphQLite\Graph;
use Kynx\GraphQLite\ValueObject\Edge;
use Kynx\GraphQLite\ValueObject\Node;

// replace with path to GraphQLite extension installed above
$extensionPath = getenv('GRAPHQLITE_EXTENSION_PATH');

// Get a connection to an in-memory graph database
$graph = Graph::connect($extensionPath, ':memory:');

// Add some nodes and edges
$graph->nodes->upsert(new Node("alice", ["name" => "Alice", "age" => 30], "Person"));
$graph->nodes->upsert(new Node("bob", ["name" => "Bob", "age" => 25], "Person"));
$graph->edges->upsert(new Edge("alice", "bob", "KNOWS", ["since" => 2020]));

// Query with Cypher
$results = $graph->query("MATCH (a:Person)-[:KNOWS]->(b) RETURN a.name AS a, b.name AS b");
foreach ($results as $row) {
    echo $row['a'] . ' knows ' . $row['b'] . "\n";
}

// outputs:
// Alice knows Bob
```

This library closely follows upstream's Python bindings. Until we've got more documentation written, GraphQLite's
excellent [documentation] should help you get started. Just remember to `camelCase` those Pythonesque `snake_case`
method names!

[GraphQLite]: https://github.com/colliery-io/graphqlite
[SQLite]: https://www.sqlite.org/
[GraphQL protocol]: https://graphql.org
[Packagist]: https://packagist.org/?query=graphql
[Cypher]: https://neo4j.com/docs/cypher-manual/current/introduction/
[documentation]: https://colliery-io.github.io/graphqlite/latest/introduction.html
