<?php

declare(strict_types=1);

use Array\KeyValueArray;
use SharedMemory\SharedMemoryManager;
use LinkedList\LinkedListManager;

class SeparateChainingMap
{
    private int $size;
    private KeyValueArray $keyValueArray;
    private LinkedListManager $linkedListManager;

    public function __construct(int $size, SharedMemoryManager $sharedMemoryManager)
    {
        $this->size = $size;
        $this->keyValueArray = new KeyValueArray($size, $sharedMemoryManager);
        $this->linkedListManager = new LinkedListManager($sharedMemoryManager);
    }

    public function put(int $key, int $value): ?int
    {
        if ($key >= $this->size) {
            throw new OutOfRangeException();
        }

        if ($value < 0) { // todo: support negative values parsing leading bit (\bindec() can't do it out of the box)
            throw new \InvalidArgumentException('Value must be greater than 0.');
        }

        $address = $this->keyValueArray->get($key)['value'];

        if ($address === null) {
            $newNode = $this->linkedListManager->createNode($value);
            $this->keyValueArray->put($key, null, $newNode->getAddress());

            return null;
        }

        $node = $this->linkedListManager->fetchNode($address);
        while ($node->getNext() !== null) {
            $node = $this->linkedListManager->fetchNode($node->getNext());
        }

        $newNode = $this->linkedListManager->createNode($value);
        $this->linkedListManager->setNext($node->getAddress(), $newNode->getAddress());

        return null;
    }

    public function get(int $key): ?int
    {
        if ($key >= $this->size) {
            throw new OutOfRangeException();
        }

        $address = $this->keyValueArray->get($key)['value'];
        if ($address === null) {
            return null;
        }

        $node = $this->linkedListManager->fetchNode($address);

        while ($node->getNext() !== null) {
            $node = $this->linkedListManager->fetchNode($node->getNext());
        }

        return $node->getValue();
    }
}
