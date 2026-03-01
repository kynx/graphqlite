<?php

declare(strict_types=1);

namespace Kynx\GqLite\Graph;

use Kynx\GqLite\ValueObject\Traversal;

interface TraversalsInterface
{
    /**
     * @return list<Traversal>
     */
    public function breadthFirst(string $startId, ?int $maxDepth = null): array;

    /**
     * @return list<Traversal>
     */
    public function depthFirst(string $startId, ?int $maxDepth = null): array;
}
