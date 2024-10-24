<?php
/**
 * Plugin Name: Pronamic Post Expiration
 * Plugin URI: https://www.pronamic.eu/plugins/pronamic-post-expiration/
 * Description: Easily manage and automate post expiration in WordPress.
 *
 * Version: 1.0.0
 * Requires at least: 5.9
 * Requires PHP: 8.1
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
 * @package   Pronamic\PostExpiration
 */

namespace Pronamic\PostExpiration;

/**
 * Plugin class
 */
class Plugin {
	/**
	 * Instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Return instance of this class.
	 *
	 * @return self A single instance of this class.
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Initialize.
	 * 
	 * @return void
	 */
	public function init() {
		
	}
}

Plugin::instance()->setup();
