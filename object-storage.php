<?php

declare(strict_types=1);

/**
 * WordPress Object Storage
 * @package     Rockschtar\WordPress\ObjectStorage
 * @author      Stefan Helmer
 * @copyright   2020-2026 Stefan Helmer
 * @license     GPL-3.0-or-later
 * @wordpress-plugin
 * Plugin Name:  WordPress Object Storage
 * Plugin URI:   https://eracer.de
 * Description:  Transients in green
 * Version:      0.1.9
 * Requires PHP: 8.4
 * Requires at least: 6.8
 * Author: Stefan Helmer
 * Author URI: https://eracer.de
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 **/

use Rockschtar\WordPress\ObjectStorage\Controller\PluginController;

if (!defined('ABSPATH')) {
    exit;
}

define('RSOS_PLUGIN_DIR', plugin_dir_path(__FILE__));
const RSOS_PLUGIN_FILE = __FILE__;

if (file_exists(RSOS_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once RSOS_PLUGIN_DIR . 'vendor/autoload.php';
}

PluginController::init();

require_once RSOS_PLUGIN_DIR . 'functions.php';
