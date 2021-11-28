<?php

declare(strict_types=1);

namespace Array;

use SharedMemory\SharedMemoryManager;
use SharedMemory\SharedMemoryArea;

final class KeyValueArray
{
    private const DATA_SIZE_BITS = 128;

    private int $size;
    private SharedMemoryArea $sharedMemoryArea;

    public function __construct(int $size, SharedMemoryManager $sharedMemoryManager)
    {
        $this->size = $size;
        $this->sharedMemoryArea = $sharedMemoryManager->allocate($size * self::DATA_SIZE_BITS);
    }

    public function put(int $index, ?int $key, int $value): void
    {
        if ($index >= $this->size) {
            throw new \OutOfRangeException();
        }

        $keyOffset = $this->calcOffset($index);
        $binKey = $key !== null ? sprintf('%064b', $key) : str_repeat(chr(0), 64);
        $this->sharedMemoryArea->write($binKey, $keyOffset);

        $valueOffset = $keyOffset + (self::DATA_SIZE_BITS / 2);
        $binValue = sprintf('%064b', $value);
        $this->sharedMemoryArea->write($binValue, $valueOffset);
    }

    public function get(int $index): array
    {
        if ($index >= $this->size) {
            throw new \OutOfRangeException();
        }

        $keyOffset = $this->calcOffset($index);
        $binKey = $this->sharedMemoryArea->read($keyOffset, self::DATA_SIZE_BITS / 2);
        $key = self::binDec($binKey);

        $valueOffset = $keyOffset + (self::DATA_SIZE_BITS / 2);
        $binValue = $this->sharedMemoryArea->read($valueOffset, self::DATA_SIZE_BITS / 2);
        $value = self::binDec($binValue);

        return ['key' => $key, 'value' => $value];
    }

    private function calcOffset(int $index): int
    {
        if ($index < 0) {
            throw new \InvalidArgumentException('Key must be >= 0.');
        }

        if ($index > $this->size) {
            throw new \OutOfRangeException();
        }

        $offset = $index > 0 ? ($index * self::DATA_SIZE_BITS) : 0;

        return $offset;
    }

    private static function binDec(string $bin): ?int
    {
        if ($bin === str_repeat(chr(0), 64)) {
            return null;
        }

        return bindec($bin);
    }
}
