<?php

use Rockschtar\WordPress\ObjectStorage\ObjectStorage;

/**
 * @param string $key
 * @param mixed $value
 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool
 */
function rsos_set_object(string $key, mixed $value, int $expiration = 0): bool {
    $objectStorage = new ObjectStorage();
    return $objectStorage->set($key, $value, $expiration);
}

/**
 * @param string $key
 */
function rsos_delete_object(string $key) : void {
    $objectStorage = new ObjectStorage();
    $objectStorage->delete($key);
}

/**
 * @param string $key
 * @return false|mixed
 */
function rsos_get_object(string $key) : mixed {
    $objectStorage = new ObjectStorage();
    return $objectStorage->get($key);
}
