<?php

declare(strict_types=1);

namespace Kynx\GraphQLite\Cypher;

use Countable;
use Kynx\GraphQLite\Exception\OutOfBoundsException;
use SeekableIterator;

use function array_values;
use function count;

/**
 * @phpstan-type CypherColumn = array<null|scalar>|null|scalar
 * @implements SeekableIterator<int, array<CypherColumn>>
 */
final class Result implements SeekableIterator, Countable
{
    /** @var list<array<CypherColumn>> $data */
    private array $data;
    private int $position;

    /**
     * @param array<array<CypherColumn>> $data
     * @param list<array-key> $columns
     */
    public function __construct(array $data, private readonly array $columns)
    {
        $this->data     = array_values($data);
        $this->position = 0;
    }

    /**
     * @return list<array-key>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return array<CypherColumn>
     */
    public function current(): array
    {
        if (! $this->valid()) {
            throw OutOfBoundsException::indexOutOfBounds($this->position);
        }

        return $this->data[$this->position];
    }

    public function next(): void
    {
        $this->position++;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function seek(int $offset): void
    {
        if (! isset($this->data[$offset])) {
            throw OutOfBoundsException::indexOutOfBounds($offset);
        }

        $this->position = $offset;
    }

    public function count(): int
    {
        return count($this->data);
    }
}
