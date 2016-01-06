<?php

require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Block.php';

class PageTitleBlock extends Block
{
	/**
	 * @method is_editable
	 * @since 0.1.0
	 */
	public function is_editable()
	{
		return false;
	}
}