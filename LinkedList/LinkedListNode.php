<?php

declare(strict_types=1);

namespace LinkedList;

final class LinkedListNode
{
    private int $value;
    private int $address;
    private ?int $next;

    public function __construct(int $value, int $address, ?int $next = null)
    {
        $this->value = $value;
        $this->address = $address;
        $this->next = $next;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getAddress(): int
    {
        return $this->address;
    }

    public function getNext(): ?int
    {
        return $this->next;
    }
}
