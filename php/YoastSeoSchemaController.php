<?php
/**
 * Yoast SEO schema controller
 *
 * @package Pronamic\WordPressPostExpiration
 */

namespace Pronamic\WordPressPostExpiration;

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

		$post_expiration_info = PostExpirationInfo::get_from_post( $post_id );

		if ( null === $post_expiration_info ) {
			return $data;
		}

		if ( null === $post_expiration_info->expiration_date ) {
			return $data;
		}

		$data['expires'] = $post_expiration_info->expiration_date->format( \DATE_ATOM );

		return $data;
	}
}
