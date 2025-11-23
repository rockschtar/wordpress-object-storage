<?php

namespace Rockschtar\WordPress\ObjectStorage\Controller;

class PluginController {

    private function __construct() {
        register_activation_hook(RSOS_PLUGIN_FILE, $this->registerCron(...));
        register_deactivation_hook(RSOS_PLUGIN_FILE, $this->unregisterCron(...));
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

    private function rescheduleCron(): void {
        $this->unregisterCron();
        $this->registerCron();
    }

    private function registerCron(): void {
        if(!wp_next_scheduled('rsos_delete_expired')) {
            wp_schedule_event(time(), 'hourly', 'rsos_delete_expired');
        }
    }

    private function unregisterCron(): void {
        wp_unschedule_event(time(), 'hourly', 'rsos_delete_expired');
    }

    private function deleteExpired(): void {

        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
                        WHERE a.option_name LIKE %s
                        AND a.option_name NOT LIKE %s
                        AND b.option_name = CONCAT( '_rsos_timeout_', SUBSTRING(a.option_name, 7))
                        AND b.option_value < %d", $wpdb->esc_like('_rsos_') . '%', $wpdb->esc_like('_rsos_timeout_') . '%', time())
        );

    }


}