<?php
namespace WPPageBlock;

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
	 * Returns the block name.
	 * @method name
	 * @since 0.1.0
	 */
	public function name()
	{
		return $this->infos['name'];
	}

	/**
	 * Returns the block description.
	 * @method description
	 * @since 0.1.0
	 */
	public function description()
	{
		return $this->infos['description'];
	}

	/**
	 * Returns whether this block can be edited.
	 * @method is_editable
	 * @since 0.1.0
	 */
	public function is_editable($block_post)
	{
		return true;
	}

	/**
	 * Returns whether this block can be deleted.
	 * @method is_deletable
	 * @since 0.1.0
	 */
	public function is_deletable($block_post)
	{
		return true;
	}

	/**
	 * Returns the block preview from within the admin.
	 * @method preview
	 * @since 0.1.0
	 */
	public function preview($block_post, $block_page)
	{
		$this->render_template($this->infos['preview_file'], array(
			'page'  => new TimberPost($block_page),
			'block' => new TimberPost($block_post)
		));
	}

	/**
	 * Renders the block.
	 * @method render
	 * @since 0.1.0
	 */
	public function render($block_post, $block_page)
	{
		$this->render_template($this->infos['template_file'], array(
			'page'  => new TimberPost($block_page),
			'block' => new TimberPost($block_post)
		));
	}

	/**
	 * Renders a specific template.
	 * @method render
	 * @since 0.1.0
	 */
	public function render_template($template, array $data = array())
	{

		Timber::$locations = array($this->infos['path']);

		$context = Timber::get_context();

		foreach ($data as $key => $val) {
			$context[$key] = $val;
		}

		$this->on_render($template, $context);

		Timber::render($template, $context);
	}

	//--------------------------------------------------------------------------
	// Events
	//--------------------------------------------------------------------------

	/**
	 * Called when the block is about to be rendered.
	 * @method on_render
	 * @since 0.1.0
	 */
	protected function on_render($template, array &$data)
	{

	}
}