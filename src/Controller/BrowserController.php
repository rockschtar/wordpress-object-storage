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
        register_rest_route('rsos/v1', '/items', array(
            'methods' => 'GET',
            'callback' => static function (WP_REST_Request $request): WP_REST_Response {
                $response = new WP_REST_Response();

                rsos_set_object('my-object', ['a' => 1]);
                rsos_set_object('my-object2', ['a' => 1], WEEK_IN_SECONDS);


                $a = ObjectStorageManager::getItems();

                $response->set_data($a);

                return $response;
            },
            'args' => array(
                'skip' => array(
                    'validate_callback' => static function ($param, $request, $key) {


                        return true;
                    },
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => static function ($value, $request, $param) {
                        return $param === 'true';
                    },
                ),
                'take' => array(
                    'validate_callback' => static function ($param, $request, $key) {


                        return true;
                    },
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => static function ($value, $request, $param) {
                        return $param === 'true';
                    },
                ),
            ),
        ));

        register_rest_route('rsos/v1', '/delete', array(
            'methods' => 'DELETE',
            'callback' => static function (WP_REST_Request $request): WP_REST_Response {
                $response = new WP_REST_Response();
                ObjectStorageManager::delete($request->get_param('name'));
                return $response;
            },
            'args' => array(
                'name' => array(
                    'validate_callback' => static function ($param, $request, $key) {
                        return true;
                    },
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
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