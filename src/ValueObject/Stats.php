<?php

declare(strict_types=1);

namespace Kynx\GqLite\ValueObject;

final readonly class Stats
{
    public function __construct(public int $nodes, public int $edges)
    {
    }
}
