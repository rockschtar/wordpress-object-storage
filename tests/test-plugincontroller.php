<?php

/**
 * Tests for the plugin controller (cron wiring).
 *
 * @package Wordpress_Object_Storage
 */

use Rockschtar\WordPress\ObjectStorage\ObjectStorage;

class PluginControllerTest extends WP_UnitTestCase
{
    public function test_cleanup_cron_is_scheduled()
    {
        // The plugin is loaded on muplugins_loaded and registers its cron on
        // init, which has already fired during the test bootstrap.
        $this->assertNotFalse(wp_next_scheduled('rsos_delete_expired'));
    }

    public function test_delete_expired_action_is_hooked()
    {
        $this->assertNotFalse(has_action('rsos_delete_expired'));
    }

    public function test_cron_hook_deletes_expired_objects()
    {
        $storage = new ObjectStorage();
        $key = 'unit_' . uniqid('', true);

        $this->assertTrue($storage->set($key, 'old', 60));
        update_option('_rsos_timeout_' . $key, time() - 10, false);

        do_action('rsos_delete_expired');

        $this->assertFalse(get_option('_rsos_' . $key));
        $this->assertFalse(get_option('_rsos_timeout_' . $key));
    }
}
