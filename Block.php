<?php
namespace WPPageBuilder;

use Timber;
use TimberPost;

class Block
{

	//--------------------------------------------------------------------------
	// Methods
	//--------------------------------------------------------------------------

	/**
	 * Renders the block.
	 * @method render
	 * @since 0.1.0
	 */
	public function render($block, $template) {

		if ($template['path'] == null) {
			trigger_error('Cannot render block with missing template path');
			return;
		}

		Timber::$locations = array($template['path']);

		$data = Timber::get_context();
		$data['block'] = new TimberPost($block->ID);

		$this->onRender($data, $block, $template);

		$template_file = $template['template_file'];

		if ($template_file == null) {
			$template_file = 'block.twig';
		}

		Timber::render($template_file, $data);
	}

	//--------------------------------------------------------------------------
	// Events
	//--------------------------------------------------------------------------

	/**
	 * Called when the block is about to be rendered.
	 * @method onRender
	 * @since 0.1.0
	 */
	protected function onRender(array &$data, $block, $template) {

	}
}