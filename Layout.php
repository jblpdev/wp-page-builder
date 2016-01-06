<?php

require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Block.php';

/**
 * @class Layout
 * @since 0.3.0
 */
class Layout extends Block
{
	/**
	 * @method get_zones
	 * @since 0.3.0
	 */
	public function get_zones()
	{
		die('You must override this method');
	}

}