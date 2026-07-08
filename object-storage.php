<?php

declare(strict_types=1);

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
 * Version:      0.1.7
 * Requires PHP: 8.4
 * Requires at least: 6.8
 * Author: Stefan Helmer
 * Author URI: https://eracer.de
 * License: MIT License
 * License URI: https://tldrlegal.com/license/mit-license
 **/

use Rockschtar\WordPress\ObjectStorage\Controller\PluginController;

define('RSOS_PLUGIN_DIR', plugin_dir_path(__FILE__));
const RSOS_PLUGIN_FILE = __FILE__;

if (file_exists(RSOS_PLUGIN_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php')) {
    require_once 'vendor/autoload.php';
}

PluginController::init();

require_once 'functions.php';
