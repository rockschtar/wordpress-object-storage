<?php

namespace Rockschtar\WordPress\ObjectStorage\Controller;

use Rockschtar\WordPress\ObjectStorage\Manager\ObjectStorageManager;
use WP_REST_Request;
use WP_REST_Response;

class BrowserController {

    private function __construct() {
        add_action('admin_menu', [&$this, 'adminMenu']);
        add_action('admin_enqueue_scripts', [&$this, 'enqueueScripts']);
        add_action('rest_api_init', [&$this, 'reqisterRoutes']);
    }

    public static function &init() {
        static $instance = false;
        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function reqisterRoutes(): void {
        register_rest_route('rsos/v1', '/get', array(
            'methods' => 'GET',
            'callback' => static function (WP_REST_Request $request): WP_REST_Response {
                $response = new WP_REST_Response();

                rsos_set_object('my-object', ['a' => 1]);

                $a = ObjectStorageManager::filter();

                $response->set_data($a);

                return $response;
            },
        ));
    }

    public function enqueueScripts($hook): void {
        if ($hook === 'settings_page_object-storage-browser') {
            wp_enqueue_script('object-storage-browser', RSOS_PLUGIN_URL . 'js/dist/index.js', ['react', 'react-dom']);

            wp_localize_script(
                'object-storage-browser',
                'ObjectStorageBrowserVariables',
                [
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
            <h1><?php esc_attr_e('Heading', 'WpAdminStyle'); ?></h1>

            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-1">


                </div>
                <!-- #post-body .metabox-holder .columns-2 -->

                <br class="clear">
            </div>
            <!-- #poststuff -->

        </div> <!-- .wrap -->
        <?php
    }

}