<?php

namespace Rockschtar\WordPress\ObjectStorage\Controller;

use Rockschtar\WordPress\ObjectStorage\Manager\ObjectStorageManager;
use WP_REST_Request;
use WP_REST_Response;

class RestController {

    private function __construct() {
        add_action('rest_api_init', [&$this, 'items']);
        add_action('rest_api_init', [&$this, 'delete']);
        add_action('rest_api_init', [&$this, 'update']);
    }

    public static function &init() {
        static $instance = false;
        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function items(): void {
        register_rest_route('rsos/v1', '/items', array(
            'methods' => 'GET',
            'callback' => static function (WP_REST_Request $request): WP_REST_Response {
                $response = new WP_REST_Response();

                $skip = $request->get_param('skip');
                $take = $request->get_param('take');
                $orderBy = $request->get_param('orderBy');
                $order = $request->get_param('order');

                $orderBy = 'expireTimestamp';

                $objectItems = ObjectStorageManager::getItems($skip, $take, $orderBy, $order);
                $response->set_data($objectItems);

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
                        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                    },
                ),
                'take' => array(
                    'validate_callback' => static function ($param, $request, $key) {
                        return true;
                    },
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => static function ($value, $request, $param) {
                        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                    },
                ),
            ),
        ));
    }

    public function delete(): void {
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
                        if (empty($param)) {
                            return false;
                        }

                        return true;
                    },
                    'required' => false,
                    'type' => 'integer',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
            ),
        ));
    }

    public function update(): void {
        register_rest_route('rsos/v1', '/update', array(
            'methods' => 'POST',
            'callback' => static function (WP_REST_Request $request): WP_REST_Response {
                $response = new WP_REST_Response();

                $name = $request->get_param('name');
                $newName = $request->get_param('newName');

                if (!empty($newName)) {
                    $objectItem = ObjectStorageManager::updateName($name, $newName);
                }

                $expireTimestamp = $request->get_param('expireTimestamp');

                $objectItem = ObjectStorageManager::updateExpireTimestamp($newName, $expireTimestamp);

                $response->set_data($objectItem);

                return $response;
            },
            'args' => array(
                'name' => array(
                    'validate_callback' => static function ($param, $request, $key) {
                        if (empty($param)) {
                            return false;
                        }

                        return true;
                    },
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),

                'newName' => array(
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),

                'value' => array(
                    'validate_callback' => static function ($param, $request, $key) {
                        if (empty($param)) {
                            return false;
                        }

                        return true;
                    },
                    'required' => false,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'expireTimestamp' => array(
                    'validate_callback' => static function ($param, $request, $key) {
                        if (empty($param)) {
                            return true;
                        }

                        if (!is_int($param)) {
                            return false;
                        }

                        return true;
                    },
                    'required' => false,
                    'type' => 'int',
                    'sanitize_callback' => static function ($value) {
                        return (int)$value;
                    },
                ),
            ),
        ));
    }

}