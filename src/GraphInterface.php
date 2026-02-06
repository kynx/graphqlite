<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Kynx\GraphQLite\Graph\EdgesInterface;
use Kynx\GraphQLite\Graph\NodesInterface;
use Kynx\GraphQLite\Graph\QueriesInterface;

// phpcs:disable Internal.ParseError.InterfaceHasMemberVar
interface GraphInterface
{
    public NodesInterface $nodes { get; }
    public EdgesInterface $edges { get; }
    public QueriesInterface $queries { get; }
}
// phpcs:enable
