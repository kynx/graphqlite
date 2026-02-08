<?php

declare(strict_types=1);

namespace Kynx\GqLite;

use Kynx\GqLite\Cypher\Result;
use Kynx\GqLite\Graph\Edges;
use Kynx\GqLite\Graph\Nodes;
use Kynx\GqLite\Graph\Queries;
use Kynx\GqLite\ValueObject\Stats;

final readonly class Graph implements GraphInterface
{
    private function __construct(
        public Nodes $nodes,
        public Edges $edges,
        private Queries $queries
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

    public function query(string $cypher, array $params = []): Result
    {
        return $this->queries->query($cypher, $params);
    }

    public function stats(): Stats
    {
        return $this->queries->stats();
    }
}
