<?php
/**
 * Tests for WordPress Object Storage.
 *
 * @package Wordpress_Object_Storage
 */

use Rockschtar\WordPress\ObjectStorage\ObjectStorage;

class ObjectStorageTest extends WP_UnitTestCase {

    public function test_set_and_get_without_expiration() {
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

    public function test_delete_removes_value_and_expiration() {
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

    public function test_get_returns_false_and_cleans_up_when_expired() {
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
}
