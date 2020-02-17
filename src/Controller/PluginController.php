<?php

namespace Rockschtar\WordPress\ObjectStorage\Controller;

use Rockschtar\WordPress\ObjectStorage\Manager\ObjectStorageManager;

class PluginController {

    private function __construct() {
        register_activation_hook(RSOS_PLUGIN_FILE, array(&$this, 'registerCron'));
        register_deactivation_hook(RSOS_PLUGIN_FILE, array(&$this, 'unregisterCron'));
        add_action('rsos_delete_expired', array(&$this, 'deleteExpired'));
        add_action('admin_action_rsos_reschedule_cron', array(&$this, 'rescheduleCron'));
        add_action('admin_action_rsos_delete_expired', array(&$this, 'deleteExpired'));
        add_action('admin_action_rsos_create', [&$this, 'createDummies']);

        ObjectStorageBrowserController::init();
        RestController::init();
    }

    public function createDummies(): void {
        for ($i = 0; $i < 201; $i++) {
            rsos_set_object('dummy-' . $i, ['a' => uniqid('k', false)]);
        }
    }

    public static function &init() {
        static $instance = false;
        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function rescheduleCron(): void {
        $this->unregisterCron();
        $this->registerCron();
    }

    public function registerCron(): void {
        if (!wp_next_scheduled('rsos_delete_expired')) {
            wp_schedule_event(time(), 'hourly', 'rsos_delete_expired');
        }
    }

    public function unregisterCron(): void {
        wp_unschedule_event(time(), 'hourly', 'rsos_delete_expired');
    }

    public function deleteExpired(): void {
        ObjectStorageManager::deleteExpired();
    }

}