<?php

declare(strict_types=1);

namespace Rockschtar\WordPress\ObjectStorage;

use DateTime;
use Rockschtar\WordPress\ObjectStorage\Models\ObjectStorageItem;

class ObjectStorage
{
    /**
     * @return false|mixed
     */
    public function get(string $key): mixed
    {
        $expirationTimestamp = get_option($this->getExpirationKey($key));

        if ($expirationTimestamp && $expirationTimestamp < time()) {
            $this->delete($key);
            return false;
        }

        return get_option($this->getKey($key));
    }

    public function getItem(string $key): ObjectStorageItem
    {
        $value = $this->get($key);
        $expirationTimestamp = $this->expires($key);
        return new ObjectStorageItem($key, $value, $expirationTimestamp);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     */
    public function set(string $key, mixed $value, int $expiration = 0): bool
    {
        $result = update_option($this->getKey($key), $value, false);

        if ($expiration > 0) {
            $timestampExpiration = time() + $expiration;
            update_option($this->getExpirationKey($key), $timestampExpiration, false);
        }

        if ($expiration === 0) {
            delete_option($this->getExpirationKey($key));
        }

        if ($expiration < 0) {
            $this->delete($key);
        }

        return $result;
    }

    public function delete($key): bool
    {
        if (delete_option($this->getKey($key)) === true) {
            return  delete_option($this->getExpirationKey($key));
        }

        return false;
    }

    public function expires($key): ?int
    {
        $value = get_option($this->getExpirationKey($key));

        if (!$value) {
            return null;
        }

        return (int) $value;
    }

    public function expiresAsDateTime($key): ?DateTime
    {
        $timestamp = $this->expires($key);

        if ($timestamp === null) {
            return null;
        }

        // Wrap DateTime instantiation in parentheses to avoid parser errors on some PHP versions
        return (new DateTime())->setTimezone(wp_timezone())->setTimestamp($timestamp);
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
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
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

    private function getExpirationKey(string $key): string
    {
        return '_rsos_timeout_' . $key;
    }

    private function getKey(string $key): string
    {
        return '_rsos_' . $key;
    }
}
