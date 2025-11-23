<?php

namespace Rockschtar\WordPress\ObjectStorage;

use DateTime;
use Rockschtar\WordPress\ObjectStorage\Models\ObjectStorageItem;

class ObjectStorage {

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

    public function getItem(string $key): ObjectStorageItem {
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
    public function set(string $key, mixed $value, int $expiration = 0): bool {
        $result = update_option($this->getKey($key), $value, false);

        if ($expiration > 0) {
            $timestampExpiration = time() + $expiration;
            update_option($this->getExpirationKey($key), $timestampExpiration, false);
        }

        if($expiration === 0) {
            delete_option($this->getExpirationKey($key));
        }

        if($expiration < 0) {
            $this->delete($key);
        }

        return $result;
    }
    
    public function delete($key): bool {
        if(delete_option($this->getKey($key)) === true) {
            return  delete_option($this->getExpirationKey($key));
        }

        return false;
    }

    public  function expires($key): ?int {
        $value = get_option($this->getExpirationKey($key));

        if (!$value) {
            return null;
        }

        return (int)$value;
    }

    public function expiresAsDateTime($key): ?DateTime {
        $timestamp = $this->expires($key);

        if ($timestamp === null) {
            return null;
        }

        return new DateTime()->setTimezone(wp_timezone())->setTimestamp($timestamp);
    }

    public function clear() : void{
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

    private function getExpirationKey(string $key): string {
        return '_rsos_timeout_' . $key;
    }

    private function getKey(string $key): string {
        return '_rsos_' . $key;
    }
}