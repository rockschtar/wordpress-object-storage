<?php

declare(strict_types=1);

namespace Rockschtar\WordPress\ObjectStorage;

use DateTime;
use Rockschtar\WordPress\ObjectStorage\Models\ObjectStorageItem;
use stdClass;

class ObjectStorage
{
    /**
     * @return false|mixed
     */
    public function get(string $key): mixed
    {
        $expirationTimestamp = get_option($this->getExpirationKey($key));

        if ($expirationTimestamp !== false && (int) $expirationTimestamp < time()) {
            $this->delete($key);
            return false;
        }

        return get_option($this->getKey($key));
    }

    public function getItem(string $key): ObjectStorageItem
    {
        $value = $this->get($key);
        $expirationTimestamp = $this->expires($key);
        $expirationDateTime = $expirationTimestamp !== null
            ? $this->timestampToDateTime($expirationTimestamp)
            : null;

        return new ObjectStorageItem($key, $value, $expirationTimestamp, $expirationDateTime);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     */
    public function set(string $key, mixed $value, int $expiration = 0): bool
    {
        if ($expiration < 0) {
            return $this->delete($key);
        }

        $optionKey = $this->getKey($key);
        $valueUpdated = update_option($optionKey, $value, false);

        if (!$valueUpdated) {
            $missing = new stdClass();
            if (get_option($optionKey, $missing) === $missing) {
                return false;
            }
        }

        if ($expiration > 0) {
            $timestampExpiration = time() + $expiration;
            $expirationUpdated = update_option($this->getExpirationKey($key), $timestampExpiration, false);

            return $valueUpdated || $expirationUpdated;
        }

        $expirationDeleted = delete_option($this->getExpirationKey($key));

        return $valueUpdated || $expirationDeleted;
    }

    public function delete(string $key): bool
    {
        $deletedValue = delete_option($this->getKey($key));
        $deletedExpiration = delete_option($this->getExpirationKey($key));

        return $deletedValue || $deletedExpiration;
    }

    public function expires(string $key): ?int
    {
        $value = get_option($this->getExpirationKey($key));

        if ($value === false || $value === '') {
            return null;
        }

        $timestamp = (int) $value;

        if ($timestamp < time()) {
            return null;
        }

        return $timestamp;
    }

    public function expiresAsDateTime(string $key): ?DateTime
    {
        $timestamp = $this->expires($key);

        if ($timestamp === null) {
            return null;
        }

        return $this->timestampToDateTime($timestamp);
    }

    /**
     * Deletes all stored objects, expired or not.
     */
    public function clear(): void
    {
        global $wpdb;

        $optionNames = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_rsos_') . '%',
            ),
        );

        // delete_option() instead of a direct DELETE keeps the options cache
        // consistent when a persistent object cache is in use.
        foreach ($optionNames as $optionName) {
            delete_option($optionName);
        }
    }

    /**
     * Deletes only objects whose expiration time has passed.
     */
    public function deleteExpired(): void
    {
        global $wpdb;

        $timeoutOptionNames = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value REGEXP '^[0-9]+$' AND CAST(option_value AS UNSIGNED) < %d",
                $wpdb->esc_like('_rsos_timeout_') . '%',
                time(),
            ),
        );

        foreach ($timeoutOptionNames as $timeoutOptionName) {
            $key = substr($timeoutOptionName, strlen('_rsos_timeout_'));
            delete_option($this->getKey($key));
            delete_option($this->getExpirationKey($key));
        }
    }

    private function timestampToDateTime(int $timestamp): DateTime
    {
        return (new DateTime())->setTimezone(wp_timezone())->setTimestamp($timestamp);
    }

    private function getExpirationKey(string $key): string
    {
        return '_rsos_timeout_' . $key;
    }

    private function getKey(string $key): string
    {
        return '_rsos_' . $key;
    }
}
