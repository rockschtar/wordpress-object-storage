<?php

use Rockschtar\WordPress\ObjectStorage\Manager\ObjectStorageManager;

/**
 * @param string $key
 * @param $value
 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool
 */
function rsos_set_object(string $key, $value, $expiration = 0): bool {
    return ObjectStorageManager::set($key, $value, $expiration);
}

/**
 * @param string $key
 */
function rsos_delete_object(string $key) {
    ObjectStorageManager::delete($key);
}

/**
 * @param string $key
 * @return bool|mixed|void
 */
function rsos_get_object(string $key) {
    return ObjectStorageManager::get($key);
}
