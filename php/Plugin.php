<?php
/**
 * Plugin
 *
 * @package Pronamic\PostExpiration
 */

namespace Pronamic\PostExpiration;

/**
 * Plugin class
 */
final class Plugin {
	/**
	 * Instance.
	 *
	 * @var self
	 */
	protected static $instance = null;

	/**
	 * Controllers.
	 * 
	 * @var array
	 */
	private $controllers = [];

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
	 * Construct.
	 */
	private function __construct() {
		$this->controllers = [
			new YoastSeoSchemaController(),
		];
	}

	/**
	 * Setup.
	 * 
	 * @return void
	 */
	public function setup() {
		\add_action( 'init', [ $this, 'init' ] );

		\add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		\add_action( 'save_post', [ $this, 'save_post' ] );

		foreach ( $this->controllers as $controller ) {
			$controller->setup();
		}
	}

	/**
	 * Initialize.
	 * 
	 * @return void
	 */
	public function init() {
		\register_post_status(
			'expired',
			[
				'label'                     => \__( 'Expired', 'pronamic-post-expiration' ),
				'exclude_from_search'       => false,
				'public'                    => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			]
		);

		\add_post_type_support( 'post', 'expiration' );
	}

	/**
	 * Add meta boxes.
	 * 
	 * @link https://developer.wordpress.org/reference/hooks/add_meta_boxes/
	 * @param string $post_type Post type.
	 * @return void
	 */
	public function add_meta_boxes( $post_type ) {
		if ( ! \post_type_supports( $post_type, 'expiration' ) ) {
			return;
		}

		\add_meta_box(
			'pronamic_post_expiration_date',
			\__( 'Expiration', 'pronamic-post-expiration' ),
			[ $this, 'meta_box_expiration' ],
			$post_type,
			'side',
			'high'
		);
	}

	/**
	 * Save post.
	 * 
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function save_post( $post_id ) {
		if ( ! \array_key_exists( 'pronamic_expiration_date_nonce', $_POST ) ) {
			return;
		}

		$nonce = \sanitize_text_field( \wp_unslash( $_POST['pronamic_expiration_date_nonce'] ) );

		if ( ! \wp_verify_nonce( $nonce, 'pronamic_save_expiration_date' ) ) {
			return;
		}

		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( ! \array_key_exists( 'pronamic_expiration_date', $_POST ) ) {
			return;
		}

		$value = \sanitize_text_field( \wp_unslash( $_POST['pronamic_expiration_date'] ) );

		\update_post_meta( $post_id, '_pronamic_expiration_date', $value );
	}

	/**
	 * Meta box expiration.
	 * 
	 * @param WP_Post $post Post.
	 * @return void
	 */
	public function meta_box_expiration( $post ) {
		$expiration_date = \get_post_meta( $post->ID, '_pronamic_expiration_date', true );

		\wp_nonce_field( 'pronamic_save_expiration_date', 'pronamic_expiration_date_nonce' );

		\printf(
			'<input type="datetime-local" name="pronamic_expiration_date" value="%s" />',
			\esc_attr( $expiration_date )
		);
	}
}
