# kynx/graphqlite

Talk to an embedded graph database with PHP.

> This project is in the early stages of development! Feel free to play, but bear in mind that there are a lot of 
> missing features!

This package provides a driver for the [SQLite]-based graph database [GraphQLite]. If you came here looking for 
something to handle the [GraphQL protocol], you're in the wrong place: try [Packagist].

## Installation

```bash
composer require kynx/graphqlite
```


GraphQLite provides an SQLLite extension that must be available locally. You will need to ensure that is installed:

```bash
brew install graphqlite       # macOS/Linux (Homebrew)
pip install graphqlite        # Python
cargo add graphqlite          # Rust
``` 

Make a note of the path it's installed `graphqlite.(dylib|so|dll)` to - you will need this later.

## Usage

GraphQList uses the [Cypher] language:

```php
use Kynx\GraphQLite\Connection;

// Get a connection to an in-memory graph database - the DSN can take anything `Pdo\Sqlite` does, minus the 
// leading `sqlite:`
$graph = Connection::connect(':memory:', '/path/to/graphqlite.(dylib|so|dll)');

// Add a node
$graph->cypher("CREATE (n:Person {name: 'Alice', age: 30})");

// Run a query
$results = $graph->cypher("MATCH (n:Person) RETURN n.name, n.age");
foreach ($result as $row) {
    echo $row['name'] . ': ' . $row['age'] . "\n";
}

// outputs:
// Alice: 30
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
