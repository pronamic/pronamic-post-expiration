<?php
/**
 * Post expiration info
 *
 * @package Pronamic\PostExpiration
 */

namespace Pronamic\PostExpiration;

/**
 * Post expiration info class
 */
final class PostExpirationInfo {
	/**
	 * Source.
	 * 
	 * @var string
	 */
	public $source = '';

	/**
	 * Show UI.
	 * 
	 * @var bool
	 */
	public $show_ui = false;

	/**
	 * Post status.
	 * 
	 * @var string
	 */
	public $post_status = 'pronamic_expired';

	/**
	 * Meta key.
	 * 
	 * @var string
	 */
	public $meta_key = '_pronamic_expiration_date';

	/**
	 * Get post expiration info from post.
	 * 
	 * @param int $post Post ID.
	 * @return self|null
	 */
	public static function get_from_post( $post ) {
		return self::get_from_post_type( \get_post_type( $post ) );
	}

	/**
	 * Get post expiration info from post type.
	 * 
	 * @param string $post_type Post type.
	 * @return self|null
	 */
	public static function get_from_post_type( $post_type ) {
		if ( ! \post_type_supports( $post_type, 'pronamic_expiration' ) ) {
			return null;
		}

		$supports = \get_all_post_type_supports( $post_type );

		$info = new self();

		if ( isset( $supports['pronamic_expiration']['source'] ) ) {
			$info->source = $supports['pronamic_expiration']['source'];
		}

		if ( isset( $supports['pronamic_expiration']['post_status'] ) ) {
			$info->post_status = $supports['pronamic_expiration']['post_status'];
		}

		if ( isset( $supports['pronamic_expiration']['show_ui'] ) ) {
			$info->show_ui = $supports['pronamic_expiration']['show_ui'];
		}

		return $info;
	}
}
