<?php

declare(strict_types=1);

namespace SharedMemory;

class SharedMemoryArea
{
    private \Shmop $shmop;
    private int $sharedMemoryOffset;
    private int $sharedMemoryLength;

    public function __construct(\Shmop $shmop, int $sharedMemoryOffset, int $sharedMemoryLength)
    {
        $this->sharedMemoryOffset = $sharedMemoryOffset;
        $this->sharedMemoryLength = $sharedMemoryLength;
        $this->shmop = $shmop;
    }

    public function write(string $data, int $offset): void
    {
        $calculatedOffset = $this->sharedMemoryOffset + $offset;
        if (($calculatedOffset + strlen($data)) > $this->sharedMemoryOffset + $this->sharedMemoryLength) {
            throw new \OutOfRangeException();
        }

        \shmop_write($this->shmop, $data, $calculatedOffset);
    }

    public function read(int $offset, int $size): string
    {
        $calculatedOffset = $this->sharedMemoryOffset + $offset;
        if (($calculatedOffset + $size) > ($this->sharedMemoryOffset + $this->sharedMemoryLength)) {
            throw new \OutOfRangeException();
        }

        return \shmop_read($this->shmop, $calculatedOffset, $size);
    }
}
