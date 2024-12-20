<?php
/**
 * Post expiration info
 *
 * @package Pronamic\WordPressPostExpiration
 */

namespace Pronamic\WordPressPostExpiration;

use DateTimeImmutable;
use DateTimeZone;

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
	 * Date format.
	 * 
	 * @var string
	 */
	public $date_format = 'Y-m-d H:i:s';

	/**
	 * Timezone.
	 * 
	 * @var string
	 */
	public $timezone = 'GMT';

	/**
	 * Expiration date.
	 * 
	 * @var null|DateTimeImmutable
	 */
	public $expiration_date;

	/**
	 * Get post expiration info from post.
	 * 
	 * @param int $post Post ID.
	 * @return self|null
	 */
	public static function get_from_post( $post ) {
		$post_object = \get_post( $post );

		$info = self::get_from_post_type( \get_post_type( $post_object ) );

		if ( null === $info ) {
			return null;
		}

		$meta_value = \get_post_meta( $post_object->ID, $info->meta_key, true );

		$info->expiration_date = self::get_expiration_date_from_meta_value( $meta_value, $info );

		return $info;
	}

	/**
	 * Get post expiration info from post type.
	 * 
	 * @param string $post_type Post type.
	 * @return self|null
	 */
	public static function get_from_post_type( $post_type ) {
		if ( ! \post_type_supports( $post_type, 'pronamic-expiration' ) ) {
			return null;
		}

		$supports = \get_all_post_type_supports( $post_type );

		$info = new self();

		if ( isset( $supports['pronamic-expiration'][0]['source'] ) ) {
			$info->source = $supports['pronamic-expiration'][0]['source'];
		}

		if ( isset( $supports['pronamic-expiration'][0]['post_status'] ) ) {
			$info->post_status = $supports['pronamic-expiration'][0]['post_status'];
		}

		if ( isset( $supports['pronamic-expiration'][0]['show_ui'] ) ) {
			$info->show_ui = $supports['pronamic-expiration'][0]['show_ui'];
		}

		if ( isset( $supports['pronamic-expiration'][0]['meta_key'] ) ) {
			$info->meta_key = $supports['pronamic-expiration'][0]['meta_key'];
		}

		return $info;
	}

	/**
	 * Get expiration date from meta value.
	 * 
	 * @param mixed $meta_value Meta value.
	 * @param self  $info       Post expiration info.
	 * @return DateTimeImmutable|null
	 */
	private static function get_expiration_date_from_meta_value( $meta_value, $info ) {
		if ( ! \is_string( $meta_value ) ) {
			return null;
		}

		try {
			$result = DateTimeImmutable::createFromFormat( $info->date_format, $meta_value, new DateTimeZone( $info->timezone ) );

			if ( false === $result ) {
				return null;
			}

			return $result;
		} catch ( \Exception $e ) {
			return null;
		}
	}
}
