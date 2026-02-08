<?php

declare(strict_types=1);

namespace Kynx\GqLite\Cypher;

use Countable;
use Kynx\GqLite\Exception\OutOfBoundsException;
use SeekableIterator;

use function array_values;
use function count;

/**
 * @template T of array
 * @implements SeekableIterator<int, T>
 */
final class Result implements SeekableIterator, Countable
{
    /** @var list<T> $data */
    private array $data;
    private int $position;

    /**
     * @param array<T> $data
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
     * @return T
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
