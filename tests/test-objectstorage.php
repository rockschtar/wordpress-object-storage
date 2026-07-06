<?php

/**
 * Tests for WordPress Object Storage.
 *
 * @package Wordpress_Object_Storage
 */

use Rockschtar\WordPress\ObjectStorage\Models\ObjectStorageItem;
use Rockschtar\WordPress\ObjectStorage\ObjectStorage;

class ObjectStorageTest extends WP_UnitTestCase
{
    public function test_set_and_get_without_expiration()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);
        $value = [ 'foo' => 'bar', 'num' => 123 ];

        $result = $storage->set($key, $value, 0);
        $this->assertTrue($result, 'set() should return true');

        $fetched = $storage->get($key);
        $this->assertSame($value, $fetched, 'get() should return the value previously set when no expiration is used');

        // Without expiration, there should be no expiration timestamp.
        $this->assertNull($storage->expires($key));
    }

    public function test_delete_removes_value_and_expiration()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        // Set with expiration to ensure both keys exist.
        $this->assertTrue($storage->set($key, 'to-delete', 60));

        // Sanity checks before delete.
        $this->assertSame('to-delete', $storage->get($key));
        $this->assertNotNull($storage->expires($key));

        // Now delete and verify cleanup.
        $this->assertTrue($storage->delete($key));
        $this->assertFalse($storage->get($key));
        $this->assertNull($storage->expires($key));
    }

    public function test_get_returns_false_and_cleans_up_when_expired()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        // Set with a value and a future expiration to create both options.
        $this->assertTrue($storage->set($key, 'will-expire', 60));
        $this->assertSame('will-expire', $storage->get($key));
        $this->assertNotNull($storage->expires($key));

        // Force expiration by setting the timeout to a past timestamp.
        // We use WP option API directly as the storage uses options internally.
        update_option('_rsos_timeout_' . $key, time() - 10, false);

        // Now the item should be considered expired: get() should return false
        // and the underlying options should be cleaned up by the storage.
        $this->assertFalse($storage->get($key));
        $this->assertNull($storage->expires($key));
    }

    public function test_set_with_negative_expiration_deletes_object()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        $this->assertTrue($storage->set($key, 'short-lived', 60));
        $this->assertSame('short-lived', $storage->get($key));

        $storage->set($key, 'short-lived', -1);

        $this->assertFalse($storage->get($key));
        $this->assertNull($storage->expires($key));
    }

    public function test_reset_without_expiration_makes_object_permanent()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        $this->assertTrue($storage->set($key, 'v1', 60));
        $this->assertNotNull($storage->expires($key));

        // Re-setting without expiration must drop the previous timeout.
        $storage->set($key, 'v2', 0);

        $this->assertNull($storage->expires($key));
        $this->assertSame('v2', $storage->get($key));
    }

    public function test_expires_returns_future_timestamp()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        $before = time();
        $this->assertTrue($storage->set($key, 'value', 3600));
        $expires = $storage->expires($key);

        $this->assertGreaterThanOrEqual($before + 3600, $expires);
        $this->assertLessThanOrEqual(time() + 3600, $expires);
    }

    public function test_expires_as_datetime()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        $this->assertTrue($storage->set($key, 'value', 3600));

        $dateTime = $storage->expiresAsDateTime($key);
        $this->assertInstanceOf(DateTime::class, $dateTime);
        $this->assertSame($storage->expires($key), $dateTime->getTimestamp());
    }

    public function test_expires_as_datetime_returns_null_without_expiration()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        $this->assertTrue($storage->set($key, 'value'));
        $this->assertNull($storage->expiresAsDateTime($key));
    }

    public function test_get_item()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);
        $value = [ 'a' => 1 ];

        $this->assertTrue($storage->set($key, $value, 3600));

        $item = $storage->getItem($key);
        $this->assertInstanceOf(ObjectStorageItem::class, $item);
        $this->assertSame($key, $item->getKey());
        $this->assertSame($value, $item->getValue());
        $this->assertSame($storage->expires($key), $item->getExpiresAtTimestamp());
    }

    public function test_delete_returns_false_for_unknown_key()
    {
        $storage = new ObjectStorage();

        $this->assertFalse($storage->delete('unit_missing_' . uniqid('', true)));
    }

    public function test_clear_deletes_all_objects_but_keeps_unrelated_options()
    {
        $storage = new ObjectStorage();
        $permanentKey = 'unit_' . uniqid('', true);
        $expiringKey = 'unit_' . uniqid('', true);

        $this->assertTrue($storage->set($permanentKey, 'permanent'));
        $this->assertTrue($storage->set($expiringKey, 'expiring', 3600));
        update_option('unrelated_option', 'keep-me', false);

        $storage->clear();

        $this->assertFalse($storage->get($permanentKey));
        $this->assertFalse($storage->get($expiringKey));
        $this->assertNull($storage->expires($expiringKey));
        $this->assertSame('keep-me', get_option('unrelated_option'));
    }

    public function test_delete_expired_removes_only_expired_objects()
    {
        $storage = new ObjectStorage();
        $expiredKey = 'unit_' . uniqid('', true);
        $validKey = 'unit_' . uniqid('', true);
        $permanentKey = 'unit_' . uniqid('', true);

        $this->assertTrue($storage->set($expiredKey, 'old', 60));
        $this->assertTrue($storage->set($validKey, 'new', 3600));
        $this->assertTrue($storage->set($permanentKey, 'forever'));

        // Force expiration of one item only.
        update_option('_rsos_timeout_' . $expiredKey, time() - 10, false);

        $storage->deleteExpired();

        // Check the raw options to make sure deleteExpired() itself cleaned
        // up, not the lazy cleanup in get().
        $this->assertFalse(get_option('_rsos_' . $expiredKey));
        $this->assertFalse(get_option('_rsos_timeout_' . $expiredKey));
        $this->assertSame('new', $storage->get($validKey));
        $this->assertSame('forever', $storage->get($permanentKey));
    }
}
