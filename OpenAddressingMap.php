<?php

declare(strict_types=1);

use Array\KeyValueArray;
use SharedMemory\SharedMemoryManager;

final class OpenAddressingMap
{
    private KeyValueArray $keyValueArray;
    private int $size;

    public function __construct(int $size, SharedMemoryManager $sharedMemoryManager)
    {
        $this->keyValueArray = new KeyValueArray($size, $sharedMemoryManager);
        $this->size = $size;
    }

    public function put(int $key, int $value): ?int
    {
        if ($key >= $this->size) {
            throw new OutOfRangeException();
        }

        if ($value < 0) { // todo: support negative values parsing leading bit (\bindec() can't do it out of the box)
            throw new \InvalidArgumentException('Value must be greater than 0.');
        }

        $index = $key % $this->size;

        while ($this->keyValueArray->get($index)['key'] !== null) {
            $index = ++$index % $this->size;

            if ($this->keyValueArray->get($index)['key'] === $index && $this->keyValueArray->get($index)['key'] !== null) {
                throw new OutOfRangeException('Out of memory');
            }
        }

        $this->keyValueArray->put($index, $key, $value);

        return null;
    }

    public function get(int $key): ?int
    {
        if ($key >= $this->size) {
            throw new OutOfRangeException();
        }

        $index = $key % $this->size;

        while ($this->keyValueArray->get($index)['key'] !== $key) {
            $index = ++$index % $this->size;

            if ($this->keyValueArray->get($index)['key'] === $index) {
                return null;
            }
        }

        return $this->keyValueArray->get($index)['value'];
    }
}
