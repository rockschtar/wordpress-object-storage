<?php

namespace Rockschtar\WordPress\ObjectStorage;

use Rockschtar\WordPress\ObjectStorage\Models\ObjectStorageItem;

class ObjectStorage {

    /**
     * @param string $key
     * @return string
     */
    private static function getExpirationKey(string $key): string {
        return '_rsos_timeout_' . $key;
    }

    /**
     * @param string $key
     * @return string
     */
    private static function getKey(string $key): string {
        return '_rsos_' . $key;
    }

    /**
     * @param string $key
     * @return bool|mixed|void
     */
    public static function get(string $key) {
        $expirationTimestamp = get_option(self::getExpirationKey($key));

        if ($expirationTimestamp && $expirationTimestamp < time()) {
            self::delete($key);
            return false;
        }

        return get_option(self::getKey($key));
    }

    public static function getItem(string $key): ObjectStorageItem {
        $objectStorageItem = new ObjectStorageItem($key);

        $objectStorageItem->setValue(self::get($key));
        $objectStorageItem->setExpiresAtDateTime(self::expiresAsDateTime($key));
        $objectStorageItem->setExpiresAtTimestamp(self::expires($key));
        return $objectStorageItem;
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     */
    public static function set(string $key, $value, $expiration = 0): bool {
        $result = update_option(self::getKey($key), $value, false);

        if ($expiration > 0) {
            $timestampExpiration = time() + $expiration;
            update_option(self::getExpirationKey($key), $timestampExpiration, false);
        }

        return $result;
    }

    /**
     * @param $key
     */
    public static function delete($key): void {
        delete_option(self::getKey($key));
        delete_option(self::getExpirationKey($key));
    }

    public static function expires($key): ?int {
        $value = get_option(self::getExpirationKey($key));

        if (!$value) {
            return null;
        }

        return (int)$value;
    }

    public static function expiresAsDateTime($key): ?\DateTime {
        $timestamp = self::expires($key);

        if ($timestamp === null) {
            return null;
        }

        return (new \DateTime())->setTimezone(wp_timezone())->setTimestamp($timestamp);
    }

    public static function delAll() {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
                        WHERE a.option_name LIKE %s
                        AND a.option_name NOT LIKE %s
                        AND b.option_name = CONCAT( '_rsos_timeout_', SUBSTRING( a.option_name, 12 ) )
                        AND b.option_value < %d", $wpdb->esc_like('_rsos_') . '%', $wpdb->esc_like('_rsos_timeout_') . '%', time())
        );
    }
}