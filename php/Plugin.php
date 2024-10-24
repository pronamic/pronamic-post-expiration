<?php
/**
 * Plugin
 *
 * @package Pronamic\PostExpiration
 */

namespace Pronamic\PostExpiration;

use DateTimeImmutable;
use DateTimezone;
use WP_Error;

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
			new SettingsController(),
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
		\add_action( 'init', [ $this, 'add_post_type_support_by_option' ], 9000 );

		\add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		\add_action( 'save_post', [ $this, 'save_post' ] );

		\add_action( 'updated_post_meta', [ $this, 'schedule_expiration_event' ], 10, 4 );
		\add_action( 'deleted_post_meta', [ $this, 'unschedule_expiration_event' ], 10, 3 );

		\add_action( 'pronamic_expire_post', [ $this, 'expire_post' ] );

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
				/* translators: %s: count value */
				'label_count'               => \_n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'pronamic-post-expiration' ),
				'exclude_from_search'       => false,
				'public'                    => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
			]
		);
	}

	/**
	 * Add post type support by option.
	 * 
	 * @return void
	 */
	public function add_post_type_support_by_option() {
		$post_types = \get_option( 'pronamic_post_expiration_post_types' );
		$post_types = \wp_parse_list( $post_types );

		foreach ( $post_types as $post_type ) {
			if ( ! \post_type_supports( $post_type, 'expiration' ) ) {
				\add_post_type_support( $post_type, 'expiration', ...[ 'source' => 'option' ] );
			}
		}
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

		$input = \sanitize_text_field( \wp_unslash( $_POST['pronamic_expiration_date'] ) );

		if ( '' === $input ) {
			\delete_post_meta( $post_id, '_pronamic_expiration_date' );
		}

		try {
			$result = DateTimeImmutable::createFromFormat( 'Y-m-d\TH:i', $input, \wp_timezone() );

			if ( false == $result ) {
				return;
			}

			$date_gmt = $result->setTimezone( new DateTimeZone( 'GMT' ) );

			\update_post_meta( $post_id, '_pronamic_expiration_date', $date_gmt->format( 'Y-m-d H:i:s' ) );
		} catch ( \Exception $e ) {
			return;
		}

		/**
		 * The `update_post_meta( … )` function call above should already trigger this,
		 * but only if the meta value has actually changed. This is an additional call
		 * to force the event to be scheduled for sure.
		 */
		$this->maybe_schedule_expiration_event( $post_id );
	}

	/**
	 * Schedule expiration event.
	 * 
	 * @link https://github.com/WordPress/WordPress/blob/1809b184049d7eacf26bc3ef68e0979a60ed7489/wp-includes/meta.php#L316-L336
	 * @param int    $meta_id     ID of updated metadata entry.
	 * @param int    $object_id   ID of the object metadata is for.
	 * @param string $meta_key    Metadata key.
	 * @param mixed  $meta_value  Metadata value.
	 */
	public function schedule_expiration_event( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( '_pronamic_expiration_date' !== $meta_key ) {
			return;
		}

		$this->maybe_schedule_expiration_event( $object_id, $meta_value );
	}

	/**
	 * Unschedule expiration event.
	 * 
	 * @link https://github.com/WordPress/WordPress/blob/1809b184049d7eacf26bc3ef68e0979a60ed7489/wp-includes/meta.php#L508-L528
	 * @param int    $meta_id     ID of updated metadata entry.
	 * @param int    $object_id   ID of the object metadata is for.
	 * @param string $meta_key    Metadata key.
	 */
	public function unschedule_expiration_event( $meta_id, $object_id, $meta_key ) {
		if ( '_pronamic_expiration_date' !== $meta_key ) {
			return;
		}

		\as_unschedule_action(
			'pronamic_expire_post',
			[
				'post_id' => $object_id,
			],
			'pronamic-post-expiration'
		);
	}

	/**
	 * Maybe schedule expiration event.
	 * 
	 * @param int         $post_id    Post ID.
	 * @param string|null $meta_value Meta value.
	 * @return void
	 */
	private function maybe_schedule_expiration_event( $post_id, $meta_value = null ) {
		if ( null === $meta_value ) {
			$meta_value = \get_post_meta( $post_id, '_pronamic_expiration_date', true );
		}

		$post_expiration_manager = new PostExpirationManager();

		$expiration_date = $post_expiration_manager->get_expiration_date_from_meta_value( $meta_value );

		if ( null === $expiration_date ) {
			return;
		}

		if ( 'expired' === \get_post_status( $post_id ) ) {
			return;
		}

		if ( ! \post_type_supports( \get_post_type( $post_id ), 'expiration' ) ) {
			return;
		}

		\as_unschedule_action(
			'pronamic_expire_post',
			[
				'post_id' => $post_id,
			],
			'pronamic-post-expiration'
		);

		$action_id = \as_schedule_single_action(
			$expiration_date->getTimestamp(),
			'pronamic_expire_post',
			[
				'post_id' => $post_id,
			],
			'pronamic-post-expiration',
			true
		);

		\update_post_meta( $post_id, '_pronamic_expire_action_id', $action_id );
	}

	/**
	 * Meta box expiration.
	 * 
	 * @param WP_Post $post Post.
	 * @return void
	 */
	public function meta_box_expiration( $post ) {
		$value = '';

		$meta_value = \get_post_meta( $post->ID, '_pronamic_expiration_date', true );

		$post_expiration_manager = new PostExpirationManager();

		$expiration_date = $post_expiration_manager->get_expiration_date_from_meta_value( $meta_value );

		if ( null !== $expiration_date ) {
			$date_local = $expiration_date->setTimezone( \wp_timezone() );

			$value = $date_local->format( 'Y-m-d\TH:i' );
		}

		\wp_nonce_field( 'pronamic_save_expiration_date', 'pronamic_expiration_date_nonce' );

		\printf(
			'<input type="datetime-local" name="pronamic_expiration_date" value="%s" />',
			\esc_attr( $value )
		);

		$action_id = \get_post_meta( $post->ID, '_pronamic_expire_action_id', true );

		if ( \current_user_can( 'manage_options' ) && \is_numeric( $action_id ) ) {
			$url = \add_query_arg(
				[
					'page' => 'action-scheduler',
				],
				\admin_url( 'tools.php' )
			);

			\printf(
				'<br><br><a href="%s">%s</a>',
				\esc_url( $url ),
				\esc_html__( 'View scheduled expire action', 'pronamic-post-expiration' )
			);
		}
	}

	/**
	 * Expire post.
	 * 
	 * @param int $post_id Post ID.
	 * @return void
	 * @throws \Exception Throws an exception if the post status could not be updated to expired.
	 */
	public function expire_post( $post_id ) {
		$meta_value = \get_post_meta( $post_id, '_pronamic_expiration_date', true );

		$post_expiration_manager = new PostExpirationManager();

		$expiration_date = $post_expiration_manager->get_expiration_date_from_meta_value( $meta_value );

		if ( null === $expiration_date ) {
			return;
		}

		if ( ! \post_type_supports( \get_post_type( $post_id ), 'expiration' ) ) {
			return;
		}

		$result = \wp_update_post(
			[
				'ID'          => $post_id,
				'post_status' => 'expired',
			],
			true
		);

		if ( $result instanceof WP_Error ) {
			throw new \Exception( \esc_html( $result->get_error_message() ) );
		}
	}
}
