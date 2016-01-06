<?php

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Block.php';
require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Layout.php';

/**
 * @function wpb_read_json
 * @since 0.1.0
 */
function wpb_read_json($file)
{
	return json_decode(file_get_contents($file), true);
}

/**
 * Returns an array that contains data about all available templates.
 * @function wpb_block_template_infos
 * @since 0.1.0
 */
function wpb_block_template_infos()
{
	$block_template_infos = array();

	foreach (wpb_block_template_paths() as $path) {

		foreach (glob($path . '/*' , GLOB_ONLYDIR) as $path) {

			$type = str_replace(WP_CONTENT_DIR, '', $path);
			$data = wpb_read_json($path . '/block.json');
			$data['buid'] = $type;
			$data['path'] = $path;

			$block_template_infos[] = $data;
		}
	}

	return apply_filters('wpb/block_template_infos', $block_template_infos);
}

/**
 * Returns an array that contains all templates path.
 * @function wpb_block_template_paths
 * @since 0.1.0
 */
function wpb_block_template_paths()
{
	return apply_filters('wpb/block_template_paths', array(WP_CONTENT_DIR . '/plugins/wp-page-block/blocks', get_template_directory() . '/blocks'));
}

/**
 * Returns the block template data using a block unique identifier. This
 * identifier is made from the block path relative to the app directory.
 * @function wpb_block_template_by_buid
 * @since 0.1.0
 */
function wpb_block_template_by_buid($block_buid)
{
	static $block_template_infos = null;

	if ($block_template_infos == null) {
		$block_template_infos = wpb_block_template_infos();
	}

	foreach ($block_template_infos as $block_template_info) {
		if ($block_template_info['buid'] == $block_buid) return $block_template_info;
	}

	return null;
}

/**
 * @function wpb_block
 * @since 0.1.0
 */
function wpb_block($block_buid, $block_post_id, $block_page_id)
{
	$block_template = wpb_block_template_by_buid($block_buid);

	if ($block_template == null) {
		return null;
	}

	$class_file = isset($block_template['class_file']) ? $block_template['class_file'] : null;
	$class_name = isset($block_template['class_name']) ? $block_template['class_name'] : null;
	require_once $block_template['path'] . '/' . $class_file;

	return new $class_name($block_post_id, $block_page_id, $block_template);
}

/**
 * @function wpb_block_edit_link
 * @since 0.3.0
 */
function wpb_block_edit_link()
{
	$block = Block::get_current();
	if ($block == null) {
		return;
	}

	if ($block->is_editable()) {
		$context = Timber::get_context();
		$context['block_post_id'] = $block->get_post_id();
		$context['block_page_id'] = $block->get_page_id();
		Timber::render('block-edit-link.twig', $context);
	}
}

/**
 * @function wpb_block_delete_link
 * @since 0.3.0
 */
function wpb_block_delete_link()
{
	$block = Block::get_current();
	if ($block == null) {
		return;
	}

	if ($block->is_deletable()) {
		$context = Timber::get_context();
		$context['block_post_id'] = $block->get_post_id();
		$context['block_page_id'] = $block->get_page_id();
		Timber::render('block-delete-link.twig', $context);
	}
}

/**
 * @function wpb_block_area
 * @since 0.3.0
 */
function wpb_block_area($area_id)
{
	$block = Block::get_current();
	if ($block == null) {
		return;
	}

	$page_id = $block->get_page_id();
	$post_id = $block->get_post_id();

	$page_blocks = get_post_meta($page_id, '_page_blocks', true);

	if ($page_blocks) {

		foreach ($page_blocks as $page_block) {

			if (!isset($page_block['block_buid']) ||
				!isset($page_block['block_page_id']) ||
				!isset($page_block['block_post_id'])) {
				continue;
			}

			if ($page_block['block_into_id'] == $post_id && $page_block['block_area_id'] == $area_id) {
				wpb_block_render_template(
					$page_block['block_buid'],
					$page_block['block_post_id'],
					$page_block['block_page_id']
				);
			}
		}
	}

	echo '<div class="button block-picker-modal-show">Add block</div>';
}

/**
 * @function wpb_block_render_outline
 * @since 0.1.0
 */
function wpb_block_render_outline($block_buid)
{
	wpb_block($block_buid, 0, 0)->render_outline();
}

/**
 * @function wpb_block_render_preview
 * @since 0.1.0
 */
function wpb_block_render_preview($block_buid, $block_post_id, $block_page_id)
{
	wpb_block($block_buid, $block_post_id, $block_page_id)->render_preview();
}

/**
 * @function wpb_block_render_template
 * @since 0.1.0
 */
function wpb_block_render_template($block_buid, $block_post_id, $block_page_id)
{
	wpb_block($block_buid, $block_post_id, $block_page_id)->render_template();
}

/**
 * @function wpb_admin_render_block_list_form
 * @since 0.3.0
 */
function wpb_admin_render_block_list_form()
{
	$block_template_infos = wpb_block_template_infos();
	$block_template_paths = wpb_block_template_paths();

	$filter = function($page_block) {
		return wpb_block_template_by_buid($page_block['block_buid']);
	};

	$page_blocks = get_post_meta(get_the_id(), '_page_blocks', true);

	if ($page_blocks) {
		$page_blocks = array_filter($page_blocks, $filter);
	}

	$data = Timber::get_context();
	$data['page_blocks'] = $page_blocks;
	$data['block_template_infos'] = $block_template_infos;
	$data['block_template_paths'] = $block_template_paths;
	Timber::render('block-list.twig', $data);
}

/**
 * @function wpb_admin_render_block_edit_form
 * @since 0.3.0
 */
function wpb_admin_render_block_edit_form()
{
	$data = Timber::get_context();
	$data['block_post_id'] = $_REQUEST['block_post_id'];
	$data['block_page_id'] = $_REQUEST['block_page_id'];
	Timber::render('block-edit.twig', $data);
}

//------------------------------------------------------------------------------
// Twig Filters
//------------------------------------------------------------------------------

TimberHelper::function_wrapper('wpb_block_edit_link');
TimberHelper::function_wrapper('wpb_block_delete_link');
TimberHelper::function_wrapper('wpb_block_render_outline');
TimberHelper::function_wrapper('wpb_block_render_preview');
TimberHelper::function_wrapper('wpb_block_render_template');
TimberHelper::function_wrapper('wpb_block_area');




