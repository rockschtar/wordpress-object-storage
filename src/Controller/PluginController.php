<?php

namespace Rockschtar\WordPress\ObjectStorage\Controller;

use Rockschtar\WordPress\ObjectStorage\ObjectStorage;

class PluginController
{
    private function __construct()
    {
        register_deactivation_hook(RSOS_PLUGIN_FILE, $this->unregisterCron(...));
        add_action('init', $this->registerCron(...));
        add_action('rsos_delete_expired', $this->deleteExpired(...));
        add_action('admin_action_rsos_reschedule_cron', $this->rescheduleCron(...));
        add_action('admin_action_rsos_delete_expired', $this->deleteExpired(...));
    }

    public static function &init(): PluginController
    {
        static $instance = false;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    private function rescheduleCron(): void
    {
        $this->unregisterCron();
        $this->registerCron();
    }

    private function registerCron(): void
    {
        if (!wp_next_scheduled('rsos_delete_expired')) {
            wp_schedule_event(time(), 'hourly', 'rsos_delete_expired');
        }
    }

    private function unregisterCron(): void
    {
        wp_clear_scheduled_hook('rsos_delete_expired');
    }

    private function deleteExpired(): void
    {
        (new ObjectStorage())->deleteExpired();
    }


}
