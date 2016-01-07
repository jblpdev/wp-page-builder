<?php
/*
Plugin Name: JBLP Page Blocks
Plugin URI: http://jblp.ca
Description: Creates pages from multiple blocks.
Version: 1.0
Author: Jean-Philippe Dery (jp@jblp.ca)
Author URI: http://jblp.ca
License: None
Copyright: JBLP Inc.
*/

require_once ABSPATH . 'wp-admin/includes/file.php';

define('WPB_VERSION', '0.1.0');
define('WPB_FILE', __FILE__);
define('WPB_DIR', plugin_dir_path(WPB_FILE));
define('WPB_URL', plugins_url('/', WPB_FILE));

require_once WP_CONTENT_DIR . '/plugins/wp-page-block/Block.php';
require_once WP_CONTENT_DIR . '/plugins/wp-page-block/lib/functions.php';

Timber::$locations = array(WPB_DIR . 'templates/');

//------------------------------------------------------------------------------
// Post Types
//------------------------------------------------------------------------------

$labels = array(
	'name'               => _x('Page Blocks', 'post type general name', 'your-plugin-textdomain' ),
	'singular_name'      => _x('Page Block', 'post type singular name', 'your-plugin-textdomain' ),
	'menu_name'          => _x('Page Blocks', 'admin menu', 'your-plugin-textdomain' ),
	'name_admin_bar'     => _x('Page Block', 'add new on admin bar', 'your-plugin-textdomain' ),
	'add_new'            => _x('Add New', 'book', 'your-plugin-textdomain' ),
	'add_new_item'       => __('Add New Page Block', 'your-plugin-textdomain' ),
	'new_item'           => __('New Page Block', 'your-plugin-textdomain' ),
	'edit_item'          => __('Edit Page Block', 'your-plugin-textdomain' ),
	'view_item'          => __('View Page Block', 'your-plugin-textdomain' ),
	'all_items'          => __('All Page Blocks', 'your-plugin-textdomain' ),
	'search_items'       => __('Search Page Blocks', 'your-plugin-textdomain' ),
	'parent_item_colon'  => __('Parent Page Blocks:', 'your-plugin-textdomain' ),
	'not_found'          => __('No page blocks found.', 'your-plugin-textdomain' ),
	'not_found_in_trash' => __('No page block found in Trash.', 'your-plugin-textdomain' )
);

register_post_type('block', array(
	'labels'             => $labels,
	'description'        => '',
	'public'             => false,
	'publicly_queryable' => false,
	'show_ui'            => true,
	'show_in_menu'       => false,
	'query_var'          => false,
	'rewrite'            => false,
	'capability_type'    => 'post',
	'has_archive'        => false,
	'hierarchical'       => false,
	'menu_position'      => null,
	'supports'           => false
));

//------------------------------------------------------------------------------
// UI
//------------------------------------------------------------------------------

/**
 * @action admin_init
 * @since 0.1.0
 */
add_action('admin_init', function() {

	/**
	 * Adds the block list metabox on the page. This metabox is hidden.
	 * @since 0.1.0
	 */
	add_meta_box('wpb_block_list', 'Blocks', function() {

		$block_template_infos = wpb_block_template_infos();
		$block_template_paths = wpb_block_template_paths();

		$filter = function($page_block) {
			return wpb_block_template_by_buid($page_block['buid']);
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

	}, 'page', 'normal', 'high');

	/**
	 * Adds a metabox on the block edit page used to store the block id and page
	 * it was added to. This metabox is hidden.
	 * @since 0.1.0
	 */
	add_meta_box('wpb_block_edit', 'Page', function() {

		$data = Timber::get_context();
		$data['post_id'] = $_REQUEST['post_id'];
		$data['page_id'] = $_REQUEST['page_id'];
		Timber::render('block-edit.twig', $data);

	}, 'block', 'normal', 'high');

	/**
	 * Styles the previous meta box.
	 * @since 0.1.0
	 */
	add_filter('postbox_classes_page_wpb_block_list', function($classes = array()){
		$classes[] = 'seamless';
		$classes[] = 'acf-postbox';
		return $classes;
	});

	/**
	 * Styles the previous meta box.
	 * @since 0.1.0
	 */
	add_filter('postbox_classes_block_wpb_block_edit', function($classes = array()) {
		$classes[] = 'hidden';
		$classes[] = 'seamless';
		$classes[] = 'acf-postbox';
		return $classes;
	});
});

/**
 * Adds the required CSS and JavaScript to the admin page.
 * @action admin_enqueue_scripts
 * @since 0.1.0
 */
add_action('admin_enqueue_scripts', function() {

	if (get_post_type() == 'page') {
		wp_enqueue_script('wpb_admin_render_block_list_form_js', WPB_URL . 'assets/js/admin-page.js', false, WPB_VERSION);
		wp_enqueue_style('wpb_admin_render_block_list_form_css', WPB_URL . 'assets/css/admin-page.css', false, WPB_VERSION);
	}

	if (get_post_type() == 'block') {
		wp_enqueue_script('wpb_admin_render_block_list_form_js', WPB_URL . 'assets/js/admin-block.js', false, WPB_VERSION);
		wp_enqueue_style('wpb_admin_block_css', WPB_URL . 'assets/css/admin-block.css', false, WPB_VERSION);
	}
});

/**
 * Moves the submit div to the bottom of the block post type page.
 * @action add_meta_boxes_block
 * @since 0.1.0
 */
add_action('add_meta_boxes_block', function() {
	remove_meta_box('submitdiv', 'block', 'side');
	add_meta_box('submitdiv', __('Save'), 'post_submit_meta_box', 'block', 'normal', 'default');
}, 0, 1);

/**
 * Renames the "Publish" button to a "Save" button on the block post type page.
 * @filter gettext
 * @since 0.1.0
 */
add_filter('gettext', function($translation, $text) {

	if (get_post_type() == 'block' && $text == 'Publish') {
		return 'Save';
	}

	return $translation;

}, 10, 2);

//------------------------------------------------------------------------------
// Post
//------------------------------------------------------------------------------

/**
 * Saves the block order.
 * @action save_post
 * @since 0.1.0
 */
add_action('save_post', function($post_id, $post) {

	if (get_post_type() == 'page' && isset($_POST['_page_blocks']) && is_array($_POST['_page_blocks'])) {

		$page_blocks = $_POST['_page_blocks'];
		$page_blocks_old = get_post_meta(get_the_id(), '_page_blocks', true);
		$page_blocks_new = array();

		foreach ($page_blocks as $post_id) {
			foreach ($page_blocks_old as $page_block_old) {
				if ($page_block_old['post_id'] == $post_id) $page_blocks_new[] = $page_block_old;
			}
		}

		update_post_meta(get_the_id(), '_page_blocks', $page_blocks_new);

	}

	return $post_id;

}, 10, 2);

/**
 * Adds a special keyword in the block post type page url that closes the page
 * when the page is saved and redirected.
 * @filter redirect_post_location
 * @since 0.1.0
 */
add_filter('redirect_post_location', function($location, $post_id) {

	switch (get_post_type()) {
		case 'block':
			$location = $location . sprintf('&post_id=%s&page_id=%s#block_saved', $_REQUEST['post_id'], $_REQUEST['page_id']);
			break;
	}

    return $location;

}, 10, 2);

/**
 * Hides the page content and displays block instead.
 * @filter the_content
 * @since 0.1.0
 */
add_filter('the_content', function($content) {

	global $post;

	if (is_admin()) {
		return $content;
	}

	if (get_post_type() == 'page') {

		$page_blocks = get_post_meta($post->ID, '_page_blocks', true);

		if ($page_blocks) {

			ob_start();

			foreach ($page_blocks as $page_block) {

				if (!isset($page_block['buid']) ||
					!isset($page_block['page_id']) ||
					!isset($page_block['post_id'])) {
					continue;
				}

				if ($page_block['into_id'] == 0) wpb_block_render_template(
					$page_block['buid'],
					$page_block['post_id'],
					$page_block['page_id']
				);
			}

			$content = ob_get_contents();

			ob_end_clean();
		}
	}

	return $content;

}, 20);

//------------------------------------------------------------------------------
// AJAX Block Management
//------------------------------------------------------------------------------

/**
 * Adds a block to a page.
 * @action wp_ajax_add_page_block
 * @since 0.1.0
 */
add_action('wp_ajax_add_page_block', function() {

	$buid = $_POST['buid'];
	$page_id = $_POST['page_id'];
	$into_id = $_POST['into_id'];
	$area_id = $_POST['area_id'];

	if (wpb_block_template_by_buid($buid) == null) {
		return;
	}

	$post_id = wp_insert_post(array(
		'post_parent'  => $page_id,
		'post_type'    => 'block',
		'post_title'   => sprintf('Page %s : Block %s', $page_id, $buid),
		'post_content' => '',
		'post_status'  => 'publish',
	));

	$page_blocks = get_post_meta($page_id, '_page_blocks', true);
	if ($page_blocks == null) {
		$page_blocks = array();
	}

	$page_block = array(
		'buid' => $buid,
		'area_id' => $area_id,
		'post_id' => (int) $post_id,
		'page_id' => (int) $page_id,
		'into_id' => (int) $into_id,
	);

	$page_blocks[] = $page_block;

	update_post_meta($page_id, '_page_blocks', $page_blocks);

	wpb_block_render_preview($buid, $post_id, $page_id);
});

/**
 * Removes a block from a page.
 * @action wp_ajax_remove_page_block
 * @since 0.1.0
 */
add_action('wp_ajax_remove_page_block', function($post_id) {

	$page_id = $_POST['page_id'];
	$post_id = $_POST['post_id'];
	// TODO: Complete
});

//------------------------------------------------------------------------------
// ACF Filters
//------------------------------------------------------------------------------

/**
 * @filter acf/settings/load_json
 * @since 0.1.0
 */
add_filter('acf/settings/load_json', function($paths) {

	static $block_template_infos = null;

	if ($block_template_infos == null) {
		$block_template_infos = wpb_block_template_infos();
	}

	foreach ($block_template_infos as $block_template_info) {
		$paths[] = $block_template_info['path'] . '/fields';
	}

	return $paths;
});

/**
 * @filter acf/get_field_groups
 * @since 0.1.0
 */
add_filter('acf/get_field_groups', function($field_groups) {

	if (get_post_type() != 'block') {
		return;
	}

	$post_id = $_GET['post_id'];
	$page_id = $_GET['page_id'];

	$page_blocks = array_filter(get_post_meta($page_id, '_page_blocks', true), function($page_block) use($post_id) {
		return $page_block['post_id'] == $post_id;
	});

	if ($page_blocks) foreach ($page_blocks as $page_block) {

		$block_template = wpb_block_template_by_buid($page_block['buid']);
		if ($block_template == null) {
			continue;
		}

		$path = $block_template['path'];

		foreach (glob("$path/fields/*.json") as $file) {

			$json = wpb_read_json($file);
			$json['ID'] = null;
			$json['location'] = array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'block'
					)
				)
			);

			$field_groups[] = $json;
		}
	}

	return $field_groups;
});
