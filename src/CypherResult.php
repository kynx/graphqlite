<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Countable;
use Iterator;
use Kynx\GraphQLite\Exception\OutOfBoundsException;
use SeekableIterator;

use function array_values;
use function count;

/**
 * @implements SeekableIterator<int, array<array|scalar>>
 */
final class CypherResult implements SeekableIterator, Countable
{
    /** @var list<array<array|scalar>> $data */
    private array $data;
    private int $position;

    /**
     * @param array<array<array|scalar>> $data
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
     * @return array<array|scalar>
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
