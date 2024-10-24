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
		\add_filter( 'wpseo_schema_graph_pieces', [ $this, 'add_graph_pieces' ], 11, 2 );
	}

	/**
	 * Add graph pieces.
	 *
	 * @param array                 $pieces  Graph pieces to output.
	 * @param \WPSEO_Schema_Context $context Object with context variables.
	 * @return array Graph pieces to output.
	 */
	public function add_graph_pieces( $pieces, $context ) {
		return $pieces;
	}
}
