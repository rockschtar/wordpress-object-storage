<?php
/**
 * WordPress Object Storage
 * @package     Rockschtar\WordPress\ObjectStorage
 * @author      Stefan Helmer
 * @copyright   2020 Stefan Helmer
 * @license     MIT
 * @wordpress-plugin
 * Plugin Name:  WordPress Object Storage
 * Plugin URI:   https://eracer.de
 * Description:  Transients in green
 * Version:      1.0.0
 * Requires PHP: 7.1
 * Requires at least: 5.2
 * Author: Stefan Helmer
 * Author URI: https://eracer.de
 * License: MIT License
 * License URI: https://tldrlegal.com/license/mit-license
 **/

define('RSOS_PLUGIN_DIR', plugin_dir_path(__FILE__));

if (file_exists(RSOS_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once 'vendor/autoload.php';
}



