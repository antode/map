<?php

declare(strict_types=1);

namespace LinkedList;

use SharedMemory\SharedMemoryArea;
use SharedMemory\SharedMemoryManager;

final class LinkedListManager
{
    private const DATA_SIZE_BITS = 128;

    private SharedMemoryArea $sharedMemoryArea;

    private int $freeSpaceOffset;

    public function __construct(SharedMemoryManager $sharedMemoryManager)
    {
        $this->sharedMemoryArea = $sharedMemoryManager->allocate($sharedMemoryManager->getFreeSpace());
        $this->freeSpaceOffset = 0;
    }

    public function createNode(int $value): LinkedListNode
    {
        $binValue = sprintf('%064b', $value);
        $binNext = str_repeat(chr(0), 64);

        $this->sharedMemoryArea->write($binValue.$binNext, $this->freeSpaceOffset);

        $linkedListNode = new LinkedListNode($value, $this->freeSpaceOffset);

        $this->freeSpaceOffset += self::DATA_SIZE_BITS + 1;

        return $linkedListNode;
    }

    public function fetchNode(int $address): LinkedListNode
    {
        $data = $this->sharedMemoryArea->read($address, self::DATA_SIZE_BITS);

        [$value, $nextAddress] = str_split($data, (self::DATA_SIZE_BITS / 2));

        [$value, $nextAddress] = [self::binDec($value), self::binDec($nextAddress)];

        if ($value === null) {
            throw new \InvalidArgumentException('Wrong address.');
        }
        
        return new LinkedListNode($value, $address, $nextAddress);
    }

    public function setNext(int $address, int $nextAddress): void
    {
        $binNext = sprintf('%064b', $nextAddress);

        $this->sharedMemoryArea->write($binNext, $address + (self::DATA_SIZE_BITS / 2));
    }

    private static function binDec(string $bin): ?int
    {
        if ($bin === str_repeat(chr(0), 64)) {
            return null;
        }

        return bindec($bin);
    }
}
