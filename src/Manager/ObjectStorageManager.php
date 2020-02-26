<?php

namespace Rockschtar\WordPress\ObjectStorage\Manager;

use Rockschtar\WordPress\ObjectStorage\Models\ObjectItem;
use Rockschtar\WordPress\ObjectStorage\Models\ObjectItems;

class ObjectStorageManager {

    private const KEY_PREFIX = '_rsos_';

    /**
     * @param string $name
     * @return string
     */
    private static function getExpirationKey(string $name): string {
        return self::KEY_PREFIX .'timeout_' . $name;
    }

    /**
     * @param string $name
     * @return string
     */
    private static function getKey(string $name): string {
        return self::KEY_PREFIX . $name;
    }

    private static function getName(string $key) : string {

        return substr($key, strlen(self::KEY_PREFIX));

    }


    /**
     * @param string $name
     * @return bool|mixed|void
     */
    public static function getValue(string $name) {
        $expirationTimestamp = (int)get_option(self::getExpirationKey($name));

        if (!empty($expirationTimestamp) && $expirationTimestamp < time()) {
            self::delete($name);
            return false;
        }

        return get_option(self::getKey($name));
    }

    public static function get(string $name): ?ObjectItem {
        $expirationTimestamp = (int)get_option(self::getExpirationKey($name));

        if (!empty($expirationTimestamp) && $expirationTimestamp < time()) {
            self::delete($name);
            return null;
        }

        if(empty($expirationTimestamp)) {
            $expirationTimestamp = null;
        }

        $value = get_option(self::getKey($name));

        return new ObjectItem($name, $value, $expirationTimestamp);

    }

    /**
     * @param string $key
     * @param $value
     * @param int $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
     * @return bool
     */
    public static function setValue(string $key, $value, $expiration = 0): bool {
        $result = update_option(self::getKey($key), $value, false);
        $timestampExpiration = 0;

        if ($expiration > 0) {
            $timestampExpiration = time() + $expiration;
        }

        update_option(self::getExpirationKey($key), $timestampExpiration, false);

        return $result;
    }

    public static function updateName(string $oldName, string $newName): ObjectItem {
        global $wpdb;
        $oldKey = self::getKey($oldName);
        $newKey = self::getKey($newName);

        $oldExpirationKey = self::getExpirationKey($oldName);
        $newExpirationKey = self::getExpirationKey($newName);

        $wpdb->update($wpdb->options, ['option_name' => $newKey], ['option_name' => $oldKey], ['%s'], ['%s']);
        $wpdb->update($wpdb->options, ['option_name' => $newExpirationKey], ['option_name' => $oldExpirationKey], ['%s'], ['%s']);

        return self::get($newName);
    }

    public static function updateExpireTimestamp(string $name, int $expireTimestamp): ObjectItem {
        global $wpdb;
        $key = self::getExpirationKey($name);
        update_option($key, $expireTimestamp);

        return self::get($name);
    }

    /**
     * @param string $name
     * @param $value
     * @param int $timestamp
     * @return bool
     */
    public static function setValueWithTimestamp(string $name, $value, $timestamp = 0): bool {
        $result = update_option(self::getKey($name), $value, false);
        update_option(self::getExpirationKey($name), $timestamp, false);
        return $result;
    }

    /**
     * @param $key
     */
    public static function delete(string $name): void {
        delete_option(self::getKey($name));
        delete_option(self::getExpirationKey($name));
    }

    public static function getItems(int $skip = 0, int $take = 20, string $orderBy = null, $order = 'asc'): ObjectItems {
        global $wpdb;

        $sqlBase = "SELECT 
                        ###fields###
                    FROM 
                        {$wpdb->options} a, {$wpdb->options} b
                    WHERE 
                        a.option_name LIKE %s
                        AND a.option_name NOT LIKE %s
                        AND b.option_name = CONCAT( '_rsos_timeout_', SUBSTRING(a.option_name, 7))";

        $sqlCount = str_replace('###fields###', 'COUNT(*)', $sqlBase);
        $totalRecords = $wpdb->get_var($wpdb->prepare($sqlCount, $wpdb->esc_like('_rsos_') . '%', $wpdb->esc_like('_rsos_timeout_') . '%'));

        $orderDirection = $order === 'desc' ? 'desc' : 'asc';

        switch ($orderBy) {
            case 'expireTimestamp':
                $orderByField = 'expire_timestamp';
                break;
            default:
                $orderByField = 'a.option_name';
        }

        $orderByClause = "ORDER BY {$orderByField} {$orderDirection}";

        $sqlRecords = str_replace('###fields###', 'a.option_id, a.option_name, a.option_value, b.option_value AS expire_timestamp', $sqlBase) . " {$orderByClause} LIMIT {$take} OFFSET {$skip}";
        $records = $wpdb->get_results($wpdb->prepare($sqlRecords, $wpdb->esc_like('_rsos_') . '%', $wpdb->esc_like('_rsos_timeout_') . '%'));

        $totalPages = $totalRecords === 0 ? 1 : ceil($totalRecords / $take);
        $currentPage = $skip === 0 ? 1 : ceil($skip / $take) + 1;

        $objectItems = new ObjectItems();
        $objectItems->setTotalPages($totalPages);
        $objectItems->setTotalItems($totalRecords);
        $objectItems->setCurrentPage($currentPage);

        foreach ($records as $record) {
            $objectItems->addItem(new ObjectItem(self::getName($record->option_name), $record->option_value, $record->expire_timestamp));
        }

        return $objectItems;
    }

    public static function deleteExpired(): void {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
                        WHERE a.option_name LIKE %s
                        AND a.option_name NOT LIKE %s
                        AND b.option_name = CONCAT( '_rsos_timeout_', SUBSTRING(a.option_name, 7))
                        AND b.option_value < %d AND b.option_value > 0", $wpdb->esc_like('_rsos_') . '%', $wpdb->esc_like('_rsos_timeout_') . '%', time())
        );
    }
}