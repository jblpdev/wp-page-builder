<?php
namespace WPPageBuilder;

use Timber;
use TimberPost;

class Block
{
	//--------------------------------------------------------------------------
	// Methods
	//--------------------------------------------------------------------------

	public $infos = array();

	//--------------------------------------------------------------------------
	// Methods
	//--------------------------------------------------------------------------

	/**
	 * @constructor
	 * @since 0.1.0
	 */
	public function __construct($infos)
	{
		$this->infos = $infos;
		$this->infos['template_file'] = isset($infos['template_file']) ? $infos['template_file'] : 'block.twig';
		$this->infos['overview_file'] = isset($infos['overview_file']) ? $infos['overview_file'] : null;
		$this->infos['block_class_file'] = isset($infos['block_class_file']) ? $infos['block_class_file'] : null;
		$this->infos['block_class_name'] = isset($infos['block_class_name']) ? $infos['block_class_name'] : null;
	}

	/**
	 * Renders the block.
	 * @method render
	 * @since 0.1.0
	 */
	public function summary($block_post)
	{
		return '';
	}

	/**
	 * Renders the block.
	 * @method render
	 * @since 0.1.0
	 */
	public function render($block_post, $block_page) {

		$data = Timber::get_context();
		$data['page'] = new TimberPost($block_page);
		$data['block'] = new TimberPost($block_post);

		$this->on_render($data);

		Timber::$locations = array($this->infos['path']);
		Timber::render($this->infos['template_file'], $data);
	}

	//--------------------------------------------------------------------------
	// Events
	//--------------------------------------------------------------------------

	/**
	 * Called when the block is about to be rendered.
	 * @method on_render
	 * @since 0.1.0
	 */
	protected function on_render(array &$data) {

	}
}