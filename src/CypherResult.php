<?php

declare(strict_types=1);

namespace Kynx\GraphQLite;

use Countable;
use Kynx\GraphQLite\Exception\OutOfBoundsException;
use SeekableIterator;

use function array_values;
use function count;

/**
 * @implements SeekableIterator<int, array>
 */
final class CypherResult implements SeekableIterator, Countable
{
    /** @var list<array> */
    private array $data;
    private int $position;

    /**
     * @param array<array-key, array> $data
     * @param list<array-key> $columns
     */
    public function __construct(array $data, private readonly array $columns)
    {
        $this->data     = array_values($data);
        $this->position = 0;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

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
