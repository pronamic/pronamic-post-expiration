<?php
/**
 * Post expiration manager
 *
 * @package Pronamic\PostExpiration
 */

namespace Pronamic\PostExpiration;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Post expiration manager class
 */
final class PostExpirationManager {
	/**
	 * Get expiration date from meta value.
	 * 
	 * @param mixed $meta_value Meta value.
	 * @return DateTimeImmutable|null
	 */
	public function get_expiration_date_from_meta_value( $meta_value ) {
		if ( ! \is_string( $meta_value ) ) {
			return null;
		}

		try {
			$result = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $meta_value, new DateTimeZone( 'GMT' ) );

			if ( false === $result ) {
				return null;
			}

			return $result;
		} catch ( \Exception $e ) {
			return null;
		}
	}
}
