<?php

require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Block.php';
require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Layout.php';

class Layout2Cols extends Layout {

	/**
	 * @method get_zones
	 * @since 0.3.0
	 */
	public function get_zones()
	{
		return array(
			'side_column' => 'Side Column',
			'main_column' => 'Main Column'
		);
	}
}