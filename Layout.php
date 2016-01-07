<?php

require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Block.php';

/**
 * @class Layout
 * @since 0.3.0
 */
class Layout extends Block
{
	/**
	 * @method is_editable
	 * @since 0.3.0
	 */
	public function is_editable()
	{
		return false;
	}

	/**
	 * Renders a specific area of this block.
	 * @method render_children
	 * @since 0.3.0
	 */
	public function render_children($area_id)
	{
		$page_id = $this->get_page_id();
		$post_id = $this->get_post_id();

		$page_blocks = get_post_meta($page_id, '_page_blocks', true);

		if ($page_blocks) {

			foreach ($page_blocks as $page_block) {

				if (!isset($page_block['buid']) ||
					!isset($page_block['page_id']) ||
					!isset($page_block['post_id'])) {
					continue;
				}

				if ($page_block['into_id'] == $post_id &&
					$page_block['area_id'] == $area_id) wpb_block_render_template(
					$page_block['buid'],
					$page_block['post_id'],
					$page_block['page_id']
				);
			}
		}
	}
}