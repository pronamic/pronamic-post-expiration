<?php
/**
 * Yoast SEO schema controller
 *
 * @package Pronamic\PostExpiration
 */

namespace Pronamic\PostExpiration;

/**
 * Yoast SEO schema controller class
 */
final class YoastSeoSchemaController {
	/**
	 * Setup.
	 *
	 * @link https://developer.yoast.com/features/schema/
	 * @return void
	 */
	public function setup() {
		\add_filter( 'wpseo_schema_article', [ $this, 'maybe_add_expires' ], 11, 1 );
		\add_filter( 'wpseo_schema_webpage', [ $this, 'maybe_add_expires' ], 11, 1 );
	}

	/**
	 * Maybe add expires.
	 *
	 * @link https://developer.yoast.com/features/schema/pieces/webpage/
	 * 
	 * @param array<string, string> $data Schema.org Webpage data array.
	 * @return array<string, string> Schema.org Webpage data array.
	 */
	public function maybe_add_expires( $data ) {
		if ( ! \is_singular() ) {
			return $data;
		}

		$post_id = \get_the_ID();

		$meta_value = \get_post_meta( $post_id, '_pronamic_expiration_date', true );

		$post_expiration_manager = new PostExpirationManager();

		$expiration_date = $post_expiration_manager->get_expiration_date_from_meta_value( $meta_value );

		if ( null === $expiration_date ) {
			return $data;
		}

		$post_expiration_info = PostExpirationInfo::get_from_post( $post_id );

		if ( null === $post_expiration_info ) {
			return $data;
		}

		$data['expires'] = $expiration_date->format( \DATE_ATOM );

		return $data;
	}
}
