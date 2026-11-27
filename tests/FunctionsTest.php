<?php

declare(strict_types=1);

/**
 * Tests for the global wrapper functions.
 *
 * @package Wordpress_Object_Storage
 */

class FunctionsTest extends WP_UnitTestCase
{
    public function test_set_get_delete_roundtrip()
    {
        $key = 'unit_' . uniqid('', true);

        $this->assertTrue(rsos_set_object($key, 'wrapped'));
        $this->assertSame('wrapped', rsos_get_object($key));

        rsos_delete_object($key);
        $this->assertFalse(rsos_get_object($key));
    }

    public function test_set_object_with_expiration()
    {
        $key = 'unit_' . uniqid('', true);

        $this->assertTrue(rsos_set_object($key, 'wrapped', 3600));
        $this->assertSame('wrapped', rsos_get_object($key));

        // Expired objects must be gone.
        update_option('_rsos_timeout_' . $key, time() - 10, false);
        $this->assertFalse(rsos_get_object($key));
    }
}
