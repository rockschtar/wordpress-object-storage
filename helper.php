<?php

/**
 * @param string $key
 * @param $value
 * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool
 */
function rsos_set_object(string $key, $value, $expiration = 0): bool {
    return \Rockschtar\WordPress\ObjectStorage\ObjectStorage::set($key, $value, $expiration);
}

/**
 * @param string $key
 */
function rsos_delete_object(string $key) {
    \Rockschtar\WordPress\ObjectStorage\ObjectStorage::delete($key);
}

/**
 * @param string $key
 * @return bool|mixed|void
 */
function rsos_get_object(string $key) {
    return \Rockschtar\WordPress\ObjectStorage\ObjectStorage::get($key);
}
