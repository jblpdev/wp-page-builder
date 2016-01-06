<?php

require_once ABSPATH . 'wp-admin/includes/file.php';

class Block
{
	//--------------------------------------------------------------------------
	// Static
	//--------------------------------------------------------------------------

	private static $current = null;

	public static function get_current() {
		return Block::$current;
	}

	//--------------------------------------------------------------------------
	// Fields
	//--------------------------------------------------------------------------

	/**
	 * @field post_id
	 * @private
	 * @since 0.3.0
	 */
	private $post_id = null;

	/**
	 * @field page_id
	 * @private
	 * @since 0.2.0
	 */
	private $page_id = null;

	/**
	 * @field infos
	 * @private
	 * @since 0.1.0
	 */
	private $infos = array();

	//--------------------------------------------------------------------------
	// Methods
	//--------------------------------------------------------------------------

	/**
	 * @constructor
	 * @since 0.1.0
	 */
	public function __construct($post_id, $page_id, $infos)
	{
		$this->post_id = $post_id;
		$this->page_id = $page_id;

		$this->infos = $infos;
		$this->infos['template_file'] = isset($infos['template_file']) ? $infos['template_file'] : 'block.twig';
		$this->infos['outline_file'] = isset($infos['outline_file']) ? $infos['outline_file'] : 'outline.twig';
		$this->infos['preview_file'] = isset($infos['preview_file']) ? $infos['preview_file'] : 'preview.twig';
		$this->infos['class_file'] = isset($infos['class_file']) ? $infos['class_file'] : null;
		$this->infos['class_name'] = isset($infos['class_name']) ? $infos['class_name'] : null;
	}

	/**
	 * Returns the block id.
	 * @method get_id
	 * @since 0.3.0
	 */
	public function get_id()
	{
		return $this->id;
	}

	/**
	 * Returns the post where the block data is stored.
	 * @method get_post_id
	 * @since 0.3.0
	 */
	public function get_post_id()
	{
		return $this->post_id;
	}

	/**
	 * Returns the page where the block is added.
	 * @method get_id
	 * @since 0.3.0
	 */
	public function get_page_id()
	{
		return $this->page_id;
	}

	/**
	 * Returns the block name.
	 * @method get_name
	 * @since 0.3.0
	 */
	public function get_name()
	{
		return $this->infos['name'];
	}

	/**
	 * Returns the block description.
	 * @method get_description
	 * @since 0.3.0
	 */
	public function get_description()
	{
		return $this->infos['description'];
	}

	/**
	 * Returns whether this block can be edited.
	 * @method is_editable
	 * @since 0.1.0
	 */
	public function is_editable()
	{
		return true;
	}

	/**
	 * Returns whether this block can be deleted.
	 * @method is_deletable
	 * @since 0.1.0
	 */
	public function is_deletable()
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
	public function render_preview()
	{
		$current = Block::$current;
		Block::$current = $this;
		$this->render($this->infos['preview_file'], Timber::get_context());
		Block::$current = $current;
	}

	/**
	 * Renders teh block template.
	 * @method render_template
	 * @since 0.1.0
	 */
	public function render_template()
	{
		$current = Block::$current;
		Block::$current = $this;
		$this->render($this->infos['template_file'], Timber::get_context());
		Block::$current = $current;
	}

	/**
	 * Renders a specific template.
	 * @method render
	 * @since 0.1.0
	 */
	public function render($template, $context)
	{
		$this->on_render($template, $context);

		$locations = Timber::$locations;

		Timber::$locations = array_merge(Timber::$locations , $this->get_render_location());

		$context['block_buid'] = $this->infos['buid'];
		$context['block_name'] = $this->infos['name'];
		$context['block_description'] = $this->infos['description'];
		$context['page_id'] = $this->page_id;
		$context['post_id'] = $this->post_id;
		$context['page'] = new TimberPost($this->page_id);
		$context['post'] = new TimberPost($this->post_id);

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
