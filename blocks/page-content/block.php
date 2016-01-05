<?php
namespace WPPageBlock;

require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Block.php';

use Timber;
use WPPageBlock\Block;

class PageContentBlock extends Block
{
	/**
	 * @method on_render
	 * @since 0.1.0
	 */
	protected function on_render($template, array &$data)
	{
		global $post;

		if ($template == 'block.twig') {
			$content = $post->post_content;
			$content = str_replace(']]>', ']]&gt;', $content);
			$data['content'] = $content;
		}
	}

	/**
	 * @method is_editable
	 * @since 0.1.0
	 */
	public function is_editable($block_post)
	{
		return false;
	}
}