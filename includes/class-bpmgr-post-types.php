<?php
/**
 * Handle Custom Post Types for OnceBlock
 *
 * @package OnceBlock
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Types Class
 */
class BPMGR_Post_Types {

	/**
	 * Instance of this class
	 *
	 * @var BPMGR_Post_Types
	 */
	private static $instance = null;

	/**
	 * Get instance
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ), 10 );
		add_action( 'admin_menu', array( $this, 'adjust_menu_labels' ), 999 );
	}

	/**
	 * Register custom post types
	 */
	public function register_post_types() {
		$this->register_block_preset_post_type();

		BPMGR_Helper_Functions::log( 'Post types registered. bpmgr_block_preset exists: ' . ( post_type_exists( 'bpmgr_block_preset' ) ? 'YES' : 'NO' ) );
	}

	/**
	 * Register Block Preset custom post type (Native WordPress)
	 */
	public function register_block_preset_post_type() {
		$labels = array(
			'name'               => __( 'Block Presets', 'onceblock' ),
			'singular_name'      => __( 'Block Preset', 'onceblock' ),
			'menu_name'          => __( 'Block Presets', 'onceblock' ),
			'name_admin_bar'     => __( 'Block Preset', 'onceblock' ),
			'add_new'            => __( 'Add New', 'onceblock' ),
			'add_new_item'       => __( 'Add New Block Preset', 'onceblock' ),
			'new_item'           => __( 'New Block Preset', 'onceblock' ),
			'edit_item'          => __( 'Edit Block Preset', 'onceblock' ),
			'view_item'          => __( 'View Block Preset', 'onceblock' ),
			'view_items'         => __( 'View Block Presets', 'onceblock' ),
			'all_items'          => __( 'All Block Presets', 'onceblock' ),
			'search_items'       => __( 'Search Block Presets', 'onceblock' ),
			'not_found'          => __( 'No block presets found.', 'onceblock' ),
			'not_found_in_trash' => __( 'No block presets found in Trash.', 'onceblock' ),
		);

		$args = array(
			'labels'       => $labels,
			'public'       => true,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-block-default',
			'supports'     => array( 'title', 'editor' ),
			'rewrite'      => array(
				'slug'       => 'block-preset',
				'with_front' => false,
			),
		);

		// Register the canonical post type slug used throughout the plugin
		$post_type_name = 'bpmgr_block_preset';
		$result         = register_post_type( $post_type_name, $args );

		if ( is_wp_error( $result ) ) {
			BPMGR_Helper_Functions::log( 'Post type registration failed: ' . $result->get_error_message() );
		}

		// Register taxonomies after post type
		$this->register_block_preset_taxonomies();
	}

	/**
	 * Register taxonomies for block presets
	 */
	public function register_block_preset_taxonomies() {
		// Block Preset Categories
		$category_labels = array(
			'name'                       => _x( 'Block Categories', 'Taxonomy General Name', 'onceblock' ),
			'singular_name'              => _x( 'Block Category', 'Taxonomy Singular Name', 'onceblock' ),
			'menu_name'                  => __( 'Categories', 'onceblock' ),
			'all_items'                  => __( 'All Categories', 'onceblock' ),
			'parent_item'                => __( 'Parent Category', 'onceblock' ),
			'parent_item_colon'          => __( 'Parent Category:', 'onceblock' ),
			'new_item_name'              => __( 'New Category Name', 'onceblock' ),
			'add_new_item'               => __( 'Add New Category', 'onceblock' ),
			'edit_item'                  => __( 'Edit Category', 'onceblock' ),
			'update_item'                => __( 'Update Category', 'onceblock' ),
			'view_item'                  => __( 'View Category', 'onceblock' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'onceblock' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'onceblock' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'onceblock' ),
			'popular_items'              => __( 'Popular Categories', 'onceblock' ),
			'search_items'               => __( 'Search Categories', 'onceblock' ),
			'not_found'                  => __( 'Not Found', 'onceblock' ),
			'no_terms'                   => __( 'No categories', 'onceblock' ),
			'items_list'                 => __( 'Categories list', 'onceblock' ),
			'items_list_navigation'      => __( 'Categories list navigation', 'onceblock' ),
		);

		$category_args = array(
			'labels'            => $category_labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => false,
			'show_in_rest'      => true,
		);

		register_taxonomy( 'bpmgr_block_category', array( 'bpmgr_block_preset' ), $category_args );

		// Block Preset Tags
		$tag_labels = array(
			'name'                       => _x( 'Block Tags', 'Taxonomy General Name', 'onceblock' ),
			'singular_name'              => _x( 'Block Tag', 'Taxonomy Singular Name', 'onceblock' ),
			'menu_name'                  => __( 'Tags', 'onceblock' ),
			'all_items'                  => __( 'All Tags', 'onceblock' ),
			'new_item_name'              => __( 'New Tag Name', 'onceblock' ),
			'add_new_item'               => __( 'Add New Tag', 'onceblock' ),
			'edit_item'                  => __( 'Edit Tag', 'onceblock' ),
			'update_item'                => __( 'Update Tag', 'onceblock' ),
			'view_item'                  => __( 'View Tag', 'onceblock' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'onceblock' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'onceblock' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'onceblock' ),
			'popular_items'              => __( 'Popular Tags', 'onceblock' ),
			'search_items'               => __( 'Search Tags', 'onceblock' ),
			'not_found'                  => __( 'Not Found', 'onceblock' ),
			'no_terms'                   => __( 'No tags', 'onceblock' ),
			'items_list'                 => __( 'Tags list', 'onceblock' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'onceblock' ),
		);

		$tag_args = array(
			'labels'            => $tag_labels,
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => false,
			'show_tagcloud'     => true,
			'show_in_rest'      => true,
		);

		register_taxonomy( 'bpmgr_block_tag', array( 'bpmgr_block_preset' ), $tag_args );
	}

	/**
	 * Adjust menu labels and ensure proper menu visibility
	 */
	public function adjust_menu_labels() {
		// Relabeling the admin menu after registration requires modifying WordPress's own
		// $menu/$submenu globals directly; there is no public API for renaming menu entries.
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		global $menu, $submenu;

		// Make sure the Block Presets menu is visible and properly labeled
		if ( post_type_exists( 'bpmgr_block_preset' ) ) {
			// Find and adjust the menu item
			foreach ( $menu as $key => $item ) {
				if ( isset( $item[2] ) && 'edit.php?post_type=bpmgr_block_preset' === $item[2] ) {
					$menu[ $key ][0] = __( 'Block Presets', 'onceblock' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					break;
				}
			}

			// Ensure submenu items are properly labeled
			if ( isset( $submenu['edit.php?post_type=bpmgr_block_preset'] ) ) {
				foreach ( $submenu['edit.php?post_type=bpmgr_block_preset'] as $key => $item ) {
					if ( 'edit.php?post_type=bpmgr_block_preset' === $item[2] ) {
						$submenu['edit.php?post_type=bpmgr_block_preset'][ $key ][0] = __( 'All Block Presets', 'onceblock' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					} elseif ( 'post-new.php?post_type=bpmgr_block_preset' === $item[2] ) {
						$submenu['edit.php?post_type=bpmgr_block_preset'][ $key ][0] = __( 'Add New Preset', 'onceblock' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					}
				}
			}
		}
	}
}
