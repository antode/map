<?php

use SharedMemory\SharedMemoryManager;

require_once 'Array/KeyValueArray.php';
require_once 'OpenAddressingMap.php';
require_once 'SeparateChainingMap.php';
require_once 'SharedMemory/SharedMemoryManager.php';
require_once 'SharedMemory/SharedMemoryArea.php';
require_once 'LinkedList/LinkedListManager.php';
require_once 'LinkedList/LinkedListNode.php';

/**
 * Тест записи и чтения OpenAddressing
 */
$sizeGb = 5;
$sharedMemoryManager = new SharedMemoryManager($sizeGb);
try {
    print "OpenAddressing\n";

    /**
     * Без коллизий
     */
    $mapSize = 1000000;
    $openAddressingMap = new OpenAddressingMap($mapSize, $sharedMemoryManager);

    $t = microtime(true);

    for ($i = 0; $i < ($mapSize); $i++) {
        $openAddressingMap->put($i, PHP_INT_MAX);
    }

    for ($i = 0; $i < ($mapSize); $i++) {
        $openAddressingMap->get($i);
    }

    var_dump(microtime(true) - $t); // 1.9931061267852783

    /**
     * С коллизиями
     */
    $sharedMemoryManager->destruct();
    $sizeGb = 5;
    $sharedMemoryManager = new SharedMemoryManager($sizeGb);

    $mapSize = 1000;
    $openAddressingMap = new OpenAddressingMap($mapSize, $sharedMemoryManager);

    $t = microtime(true);

    for ($i = 0; $i < ($mapSize - 1); $i++) {
        $openAddressingMap->put(0, PHP_INT_MAX);
    }

    $openAddressingMap->put(1, PHP_INT_MAX);

    $openAddressingMap->get(1);

    var_dump(microtime(true) - $t); // 0.544464111328125
} finally {
    $sharedMemoryManager->destruct();
}

/**
 * Тест записи и чтения SeparateChaining
 */
try {
    echo "SeparateChaining\n";

    /**
     * Без коллизий
     */
    $sharedMemoryManager->destruct();
    $sizeGb = 5;
    $sharedMemoryManager = new SharedMemoryManager($sizeGb);

    $mapSize = 1000000;
    $separateChainingMap = new SeparateChainingMap($mapSize, $sharedMemoryManager);

    $t = microtime(true);

    for ($i = 0; $i < ($mapSize); $i++) {
        $separateChainingMap->put($i, PHP_INT_MAX);
    }

    for ($i = 0; $i < ($mapSize); $i++) {
        $separateChainingMap->get($i);
    }

    var_dump(microtime(true) - $t); // 1.3169989585876465

    /**
     * С коллизиями
     */
    $sharedMemoryManager->destruct();
    $sizeGb = 5;
    $sharedMemoryManager = new SharedMemoryManager($sizeGb);

    $mapSize = 1000;
    $separateChainingMap = new SeparateChainingMap($mapSize, $sharedMemoryManager);

    $t = microtime(true);

    for ($i = 0; $i < ($mapSize); $i++) {
        $separateChainingMap->put(0, PHP_INT_MAX);
    }

    $separateChainingMap->get(0);

    var_dump(microtime(true) - $t); // 0.3132009506225586
} finally {
    $sharedMemoryManager->destruct();
}
