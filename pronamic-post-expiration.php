<?php
/**
 * Plugin Name: Pronamic Post Expiration
 * Plugin URI: https://www.pronamic.eu/plugins/pronamic-post-expiration/
 * Description: Easily manage and automate post expiration in WordPress.
 *
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 8.2
 *
 * Author: Pronamic
 * Author URI: https://www.pronamic.eu/
 *
 * Text Domain: pronamic-post-expiration
 * Domain Path: /languages/
 *
 * License: proprietary
 *
 * GitHub URI: https://github.com/pronamic/pronamic-post-expiration
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2024 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPressPostExpiration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload.
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Action Scheduler.
 */
require_once __DIR__ . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

/**
 * Plugin.
 */
add_action(
	'plugins_loaded',
	function () {
		load_plugin_textdomain( 'pronamic-post-expiration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
);

\Pronamic\WordPressPostExpiration\Plugin::instance()->setup();
