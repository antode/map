<?php

declare(strict_types=1);

namespace SharedMemory;

class SharedMemoryManager
{
    private \Shmop $shmop;
    private int $freeSpaceOffset;

    public function __construct(int $sizeGb)
    {
        $this->shmop = \shmop_open(1, 'c', 0644, $sizeGb * 1000 * 1000 * 1000);
        $this->freeSpaceOffset = 0;
    }

    public function allocate(int $size): SharedMemoryArea
    {
        if ($this->freeSpaceOffset + $size > shmop_size($this->shmop)) {
            throw new \OutOfRangeException();
        }

        $sharedMemoryArea = new SharedMemoryArea($this->shmop, $this->freeSpaceOffset, $size);

        $this->freeSpaceOffset += ($size + 1);

        return $sharedMemoryArea;
    }

    public function getFreeSpace(): int
    {
        return shmop_size($this->shmop) - $this->freeSpaceOffset;
    }

    public function destruct(): void
    {
        \shmop_delete($this->shmop);
    }
}
