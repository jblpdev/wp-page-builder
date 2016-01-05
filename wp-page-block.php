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

	}, 'page', 'normal', 'high');

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
	 * Adds a metabox on the block edit page used to store the block id and page
	 * it was added to. This metabox is hidden.
	 * @since 0.1.0
	 */
	add_meta_box('wpb_block_edit', 'Page', function() {

		$data = Timber::get_context();
		$data['block_id'] = $_REQUEST['block_id'];
		$data['block_page'] = $_REQUEST['block_page'];
		Timber::render('block-edit.twig', $data);

	}, 'block', 'normal', 'high');

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
		wp_enqueue_script('wpb_admin_page_js', WPB_URL . 'assets/js/admin-page.js', false, WPB_VERSION);
		wp_enqueue_style('wpb_admin_page_css', WPB_URL . 'assets/css/admin-page.css', false, WPB_VERSION);
	}

	if (get_post_type() == 'block') {
		wp_enqueue_script('wpb_admin_page_js', WPB_URL . 'assets/js/admin-block.js', false, WPB_VERSION);
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
 * Saves the block that were added / removed on a page.
 * @action save_post
 * @since 0.1.0
 */
add_action('save_post', function($post_id, $post) {

	if (get_post_type() == 'page' && isset($_POST['_page_blocks_id']) && is_array($_POST['_page_blocks_id'])) {

		$page_blocks_id = $_POST['_page_blocks_id'];
		$page_blocks_old = get_post_meta(get_the_id(), '_page_blocks', true);
		$page_blocks_new = array();

		foreach ($page_blocks_id as $page_block_id) {
			foreach ($page_blocks_old as $page_block_old) {
				if ($page_block_old['block_id'] === $page_block_id) $page_blocks_new[] = $page_block_old;
			}
		}

		update_post_meta($post_id, '_page_blocks', $page_blocks_new);
	}

	/*
	if (get_post_type() == 'block') {

	}
	*/

	return $post_id;

}, 10, 2);

/**
 * @action delete_post
 * @since 0.1.0
 */
add_action('delete_post', function($post_id) {

}, 10);

/**
 * Adds a special keyword in the block post type page url that closes the page
 * when the page is saved and redirected.
 * @filter redirect_post_location
 * @since 0.1.0
 */
add_filter('redirect_post_location', function($location, $post_id) {

	switch (get_post_type()) {
		case 'block':
			$location = $location . sprintf('&block_id=%s&block_page=%s#block_saved', $_REQUEST['block_id'], $_REQUEST['block_page']);
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

	if (get_post_type() == 'page' && wpb_page_has_blocks()) {
		ob_start();
		wpb_build();
		$content = ob_get_contents();
		ob_end_clean();
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

	$block_page = $_POST['block_page'];
	$block_buid = $_POST['block_buid'];

	if (wpb_block_template_by_buid($block_buid) == null) {
		return;
	}

	$block_post = array(
		'post_parent'  => $block_page,
		'post_type'    => 'block',
		'post_title'   => sprintf('Page %s : Block %s', $block_page, $block_buid),
		'post_content' => '',
		'post_status'  => 'publish',
	);

	$block_post = wp_insert_post($block_post);

	$page_blocks = get_post_meta($block_page, '_page_blocks', true);
	if ($page_blocks == null) {
		$page_blocks = array();
	}

	$block_id = uniqid();

	$page_block = array(
		'block_id' => $block_id,
		'block_post' => $block_post,
		'block_page' => $block_page,
		'block_buid' => $block_buid
	);

	$page_blocks[] = $page_block;

	update_post_meta($block_page, '_page_blocks', $page_blocks);

	$data = Timber::get_context();
	$data['page_block'] = $page_block;
	Timber::render('block-list-item.twig', $data);
});

/**
 * Removes a block from a page.
 * @action wp_ajax_remove_page_block
 * @since 0.1.0
 */
add_action('wp_ajax_remove_page_block', function($block_id) {

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

	$block_id = $_GET['block_id'];
	$block_page = $_GET['block_page'];

	$page_blocks = array_filter(get_post_meta($block_page, '_page_blocks', true), function($page_block) use($block_id) {
		return $page_block['block_id'] == $block_id;
	});

	if ($page_blocks) foreach ($page_blocks as $page_block) {

		$block_template = wpb_block_template_by_buid($page_block['block_buid']);
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

//------------------------------------------------------------------------------
// Functions
//------------------------------------------------------------------------------

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
	return apply_filters('wpb/block_template_paths', array(dirname(__FILE__) . '/blocks', get_template_directory() . '/blocks'));
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
 * Returns the block name from the block configs.
 * @function wpb_block_name
 * @since 0.1.0
 */
function wpb_block_name($block_template_buid)
{
	return wpb_block_instance($block_template_buid)->name();
}

/**
 * Returns the block descript from the block configs.
 * @function wpb_block_description
 * @since 0.1.0
 */
function wpb_block_description($block_template_buid)
{
	return wpb_block_instance($block_template_buid)->description();
}

/**
 * Returns the block preview for a specific page within the admin.
 * @function wpb_block_preview
 * @since 0.1.0
 */
function wpb_block_preview($block_template_buid, $block_post, $block_page)
{
	return wpb_block_instance($block_template_buid)->preview($block_post, $block_page);
}

/**
 * Indicates whether the block is editable.
 * @function wpb_block_is_editable
 * @since 0.1.0
 */
function wpb_block_is_editable($block_template_buid, $block_post)
{
	return wpb_block_instance($block_template_buid)->is_editable($block_post);
}

/**
 * Indicates whether the block is deletable.
 * @function wpb_block_is_deletable
 * @since 0.1.0
 */
function wpb_block_is_deletable($block_template_buid, $block_post)
{
	return wpb_block_instance($block_template_buid)->is_deletable($block_post);
}

/**
 * @function wpb_block_overview
 * @since 0.1.0
 */
function wpb_block_overview($block_template_buid)
{
	$block_template = wpb_block_template_by_buid($block_template_buid);
	if ($block_template == null) {
		echo 'Unavailable';
		return;
	}

	echo file_get_contents($block_template['path'] . '/' . $block_template['overview_file']);
}

/**
 * @function wpb_block_instance
 * @since 0.1.0
 */
function wpb_block_instance($block_template_buid)
{
	static $instances = array();

	$block_instance = isset($instances[$block_template_buid]) ? $instances[$block_template_buid] : null;

	if ($block_instance == null) {

		$block_template = wpb_block_template_by_buid($block_template_buid);

		if ($block_template == null) {
			return null;
		}

		$block_class_file = isset($block_template['block_class_file']) ? $block_template['block_class_file'] : null;
		$block_class_name = isset($block_template['block_class_name']) ? $block_template['block_class_name'] : null;
		$block_class_instance = null;

		if ($block_class_file && $block_class_name) {
			require_once $block_template['path'] . '/' . $block_class_file;
			$block_instance = new $block_class_name($block_template);
		} else {
			$block_instance = new WPPageBlock\Block\Block($block_template);
		}

		$instances[$block_template_buid] = $block_instance;
	}

	return $block_instance;
}

/**
 * @function wpb_page_has_blocks
 * @since 0.1.0
 */
function wpb_page_has_blocks($page_id = null)
{
	global $post;

	$page = get_post($page_id);

	$page_blocks = get_post_meta($page->ID, '_page_blocks', true);

	return $page_blocks && count($page_blocks);
}

/**
 * @function wpb_build
 * @since 0.1.0
 */
function wpb_build($page_id = null)
{
	global $post;

	$page = get_post($page_id);

	$page_blocks = get_post_meta($page->ID, '_page_blocks', true);

	if ($page_blocks) foreach ($page_blocks as $page_block) {

		if (!isset($page_block['block_id']) ||
			!isset($page_block['block_page']) ||
			!isset($page_block['block_post']) ||
			!isset($page_block['block_buid'])) {
			continue;
		}

		$block_id = $page_block['block_id'];
		$block_page = $page_block['block_page'];
		$block_post = $page_block['block_post'];
		$block_buid = $page_block['block_buid'];

		$locations = \Timber::$locations;

		if ($block_instance = wpb_block_instance($block_buid)) {
			$block_instance->render(
				$block_post,
				$block_page
			);
		}

		\Timber::$locations = $locations;
	}
}
