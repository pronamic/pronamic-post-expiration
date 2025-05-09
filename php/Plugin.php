<?php
/**
 * Plugin
 *
 * @package Pronamic\WordPressPostExpiration
 */

namespace Pronamic\WordPressPostExpiration;

use DateTimeImmutable;
use DateTimezone;
use WP_Error;
use WP_Post;
use WP_REST_Response;

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
	 * @var array<object>
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
		\add_action( 'init', $this->register_post_status( ... ), 9000 );
		\add_action( 'init', $this->add_post_type_support_by_option( ... ), 9000 );

		\add_action( 'add_meta_boxes', $this->add_meta_boxes( ... ) );

		\add_action( 'save_post', $this->save_post( ... ) );

		\add_action( 'added_post_meta', $this->schedule_expiration_event( ... ), 10, 3 );
		\add_action( 'updated_post_meta', $this->schedule_expiration_event( ... ), 10, 3 );
		\add_action( 'deleted_post_meta', $this->unschedule_expiration_event( ... ), 10, 3 );

		\add_action( 'pronamic_expire_post', $this->expire_post( ... ) );

		foreach ( $this->controllers as $controller ) {
			$controller->setup();
		}
	}

	/**
	 * Register post status.
	 *
	 * @return void
	 */
	private function register_post_status() {
		$post_status_info = \get_post_status_object( 'pronamic_expired' );

		if ( null !== $post_status_info ) {
			return;
		}

		\register_post_status(
			'pronamic_expired',
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
	private function add_post_type_support_by_option() {
		$config = \get_option( 'pronamic_post_expiration_config' );

		if ( ! isset( $config['post_types'] ) ) {
			return;
		}

		if ( ! \is_array( $config['post_types'] ) ) {
			return;
		}

		$post_types_config = $config['post_types'];

		$post_types_config = \array_filter(
			$post_types_config,
			fn( $post_type_config ) => $post_type_config['support'] ?? false
		);

		foreach ( $post_types_config as $post_type => $post_type_config ) {
			if ( ! \post_type_supports( $post_type, 'pronamic-expiration' ) ) {
				\add_post_type_support(
					$post_type,
					'pronamic-expiration',
					[
						'source'      => 'option',
						'show_ui'     => true,
						'post_status' => $post_type_config['post_status'] ?? 'pronamic_expired',
					]
				);
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
	private function add_meta_boxes( $post_type ) {
		$post_expiration_info = PostExpirationInfo::get_from_post_type( $post_type );

		if ( null === $post_expiration_info ) {
			return;
		}

		if ( ! $post_expiration_info->show_ui ) {
			return;
		}

		\add_meta_box(
			'pronamic_post_expiration_date',
			\__( 'Expiration', 'pronamic-post-expiration' ),
			$this->meta_box_expiration( ... ),
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
	private function save_post( $post_id ) {
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
		} catch ( \Exception ) {
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
	 * @param int    $meta_id   ID of updated metadata entry.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key  Metadata key.
	 * @return void
	 */
	private function schedule_expiration_event( $meta_id, $object_id, $meta_key ) {
		$post_expiration_info = PostExpirationInfo::get_from_post( $object_id );

		if ( null === $post_expiration_info ) {
			return;
		}

		if ( $post_expiration_info->meta_key !== $meta_key ) {
			return;
		}

		$this->maybe_schedule_expiration_event( $object_id );
	}

	/**
	 * Unschedule expiration event.
	 *
	 * @link https://github.com/WordPress/WordPress/blob/1809b184049d7eacf26bc3ef68e0979a60ed7489/wp-includes/meta.php#L508-L528
	 * @param int    $meta_id     ID of updated metadata entry.
	 * @param int    $object_id   ID of the object metadata is for.
	 * @param string $meta_key    Metadata key.
	 * @return void
	 */
	private function unschedule_expiration_event( $meta_id, $object_id, $meta_key ) {
		$post_expiration_info = PostExpirationInfo::get_from_post( $object_id );

		if ( null === $post_expiration_info ) {
			return;
		}

		if ( $post_expiration_info->meta_key !== $meta_key ) {
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
	 * @param int $post_id Post ID.
	 * @return void
	 */
	private function maybe_schedule_expiration_event( $post_id ) {
		$post_expiration_info = PostExpirationInfo::get_from_post( $post_id );

		if ( null === $post_expiration_info ) {
			return;
		}

		if ( null === $post_expiration_info->expiration_date ) {
			return;
		}

		if ( \get_post_status( $post_id ) === $post_expiration_info->post_status ) {
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
			$post_expiration_info->expiration_date->getTimestamp(),
			'pronamic_expire_post',
			[
				'post_id' => $post_id,
			],
			'pronamic-post-expiration'
		);

		\update_post_meta( $post_id, '_pronamic_expire_action_id', $action_id );
	}

	/**
	 * Meta box expiration.
	 *
	 * @param WP_Post $post Post.
	 * @return void
	 */
	private function meta_box_expiration( $post ) {
		$value = '';

		$post_expiration_info = PostExpirationInfo::get_from_post( $post_id );

		if ( null !== $post_expiration_info && null !== $post_expiration_info->expiration_date ) {
			$date_local = $post_expiration_info->expiration_date->setTimezone( \wp_timezone() );

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
	private function expire_post( $post_id ) {
		$post_expiration_info = PostExpirationInfo::get_from_post( $post_id );

		if ( null === $post_expiration_info ) {
			return;
		}

		if ( null === $post_expiration_info->expiration_date ) {
			return;
		}

		if ( '' === $post_expiration_info->post_status ) {
			return;
		}

		$result = \wp_update_post(
			[
				'ID'          => $post_id,
				'post_status' => $post_expiration_info->post_status,
			],
			true
		);

		if ( $result instanceof WP_Error ) {
			throw new \Exception( \esc_html( $result->get_error_message() ) );
		}
	}
}
