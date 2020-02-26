<?php

namespace Rockschtar\WordPress\ObjectStorage\Controller;

class ObjectStorageBrowserController {

    private function __construct() {
        add_action('admin_menu', [&$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [&$this, 'enqueueScripts']);
    }

    public static function &init() {
        static $instance = false;
        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function enqueueScripts($hook): void {
        if ($hook === 'settings_page_object-storage-browser') {
            wp_enqueue_script('object-storage-browser', RSOS_PLUGIN_URL . 'js/dist/index.js', ['wp-i18n', 'react', 'react-dom']);
            wp_enqueue_style('object-storage-browser', RSOS_PLUGIN_URL . 'js/dist/light.css');
            wp_localize_script(
                'object-storage-browser',
                'ObjectStorageBrowserVariables',
                [
                    'locale' => strtolower(explode('_', get_locale())[0]),
                    'resturl' => esc_url_raw(rest_url('rsos/v1/')),
                    'nonce' => wp_create_nonce('wp_rest')]
            );
        }
    }

    public function adminMenu(): void {
        $hook = add_submenu_page('options-general.php', 'Object Storage Browser', 'Object Storage', 'manage_options', 'object-storage-browser', [&$this, 'page']);
    }

    public function page(): void {
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"></div>
            <h1><?php esc_attr_e('Object Storage', 'rsos'); ?></h1>
            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-1">

                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }

}