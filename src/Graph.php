<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Kynx\GraphQLite\Graph\Edges;
use Kynx\GraphQLite\Graph\Nodes;
use Kynx\GraphQLite\Graph\Queries;

final readonly class Graph implements GraphInterface
{
    private function __construct(
        public Nodes $nodes,
        public Edges $edges,
        public Queries $queries
    ) {
    }

    public static function connect(string $extensionPath, string $database = Connection::MEMORY): self
    {
        return self::getInstance(Connection::connect($extensionPath, $database));
    }

    public static function getInstance(ConnectionInterface $connection): self
    {
        return new self(
            new Nodes($connection),
            new Edges($connection),
            new Queries($connection)
        );
    }
}
