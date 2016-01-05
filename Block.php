<?php
namespace WPPageBlock;

use Timber;
use TimberPost;
use TimberHelper;

class Block
{
	//--------------------------------------------------------------------------
	// Fields
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
		$this->infos['outline_file'] = isset($infos['outline_file']) ? $infos['outline_file'] : 'outline.twig';
		$this->infos['preview_file'] = isset($infos['preview_file']) ? $infos['preview_file'] : 'preview.twig';
		$this->infos['template_file'] = isset($infos['template_file']) ? $infos['template_file'] : 'block.twig';
		$this->infos['class_file'] = isset($infos['class_file']) ? $infos['class_file'] : null;
		$this->infos['class_name'] = isset($infos['class_name']) ? $infos['class_name'] : null;
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
	 * Renders the outline template.
	 * @method render_outline
	 * @since 0.1.0
	 */
	public function render_outline()
	{
		$this->render($this->infos['outline_file'], Timber::get_context());
	}

	/**
	 * Renders the block preview template.
	 * @method render_preview
	 * @since 0.1.0
	 */
	public function render_preview($block_page, $block_post, $block_id)
	{
		$context = Timber::get_context();
		$context['page']  = new TimberPost($block_page);
		$context['block'] = new TimberPost($block_post);
		$context['block_id'] = $block_id;
		$this->render($this->infos['preview_file'], $context);
	}

	/**
	 * Renders teh block template.
	 * @method render_template
	 * @since 0.1.0
	 */
	public function render_template($block_page, $block_post, $block_id)
	{
		$context = Timber::get_context();
		$context['page']  = new TimberPost($block_page);
		$context['block'] = new TimberPost($block_post);
		$context['block_id'] = $block_id;
		$this->render($this->infos['template_file'], $context);
	}

	/**
	 * Renders a specific template.
	 * @method render
	 * @since 0.1.0
	 */
	public function render($template, $context)
	{
		$this->on_render($template, $context);

		$locations = \Timber::$locations;

		Timber::$locations = array_merge(Timber::$locations , $this->get_render_location());

		$context['block_buid'] = $this->infos['buid'];
		$context['block_name'] = $this->infos['name'];
		$context['block_description'] = $this->infos['description'];
		$context['render_block_edit_link'] = TimberHelper::function_wrapper('render_block_edit_link');
		$context['render_block_delete_link'] = TimberHelper::function_wrapper('render_block_delete_link');

		Timber::render($template, $context);

		Timber::$locations = $locations;
	}

	/**
	 * Returns the template locations.
	 * @method get_render_location
	 * @since 0.1.0
	 */
	public function get_render_location()
	{
		return array($this->infos['path']);
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