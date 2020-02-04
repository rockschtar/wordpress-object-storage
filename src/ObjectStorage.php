<?php

namespace Rockschtar\WordPress\ObjectStorage;

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
     * @return bool|mixed|void
     */
    public static function get(string $key) {

        $expirationTimestamp = get_option(self::getExpirationKey($key));

        if ($expirationTimestamp && $expirationTimestamp < time()) {
            self::delete($key);
            return false;
        }

        return get_option($key);
    }

    /**
     * @param string $key
     * @param $value
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     */
    public static function set(string $key, $value, $expiration = 0): bool {
        $result = update_option($key, $value, false);

        if ($expiration > 0) {
            $timestampExpiration = time() + $expiration;
            update_option('_rsos_timeout_' . $key, $timestampExpiration, false);
        }

        return $result;
    }

    /**
     * @param $key
     */
    public static function delete($key): void {
        delete_option($key);
        delete_option($key . '-expiration');
    }

    public static function delAll() {

        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
WHERE a.option_name LIKE %s
AND a.option_name NOT LIKE %s
AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
AND b.option_value < %d",
                $wpdb->esc_like('_transient_') . '%',
                $wpdb->esc_like('_transient_timeout_') . '%',
                time()
            )
        );

    }


}