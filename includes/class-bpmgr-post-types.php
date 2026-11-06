<?php
/**
 * Handle Custom Post Types for Block Presets Manager
 *
 * @package Block_Presets_Manager
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
		$settings = get_option( 'bpmgr_settings', array() );

		// Always register the new post type
		$this->register_block_preset_post_type();

		// Register legacy post type for backward compatibility if enabled
		$enable_legacy = isset( $settings['enable_legacy_post_type'] ) ? $settings['enable_legacy_post_type'] : true;
		if ( $enable_legacy ) {
			$this->register_legacy_block_post_type();
		}

		BPMGR_Helper_Functions::log( 'Post types registered. bpmgr_block_preset exists: ' . ( post_type_exists( 'bpmgr_block_preset' ) ? 'YES' : 'NO' ) );
	}

	/**
	 * Register Block Preset custom post type (Native WordPress)
	 */
	public function register_block_preset_post_type() {
		$labels = array(
			'name'               => __( 'Block Presets', 'block-presets-manager' ),
			'singular_name'      => __( 'Block Preset', 'block-presets-manager' ),
			'menu_name'          => __( 'Block Presets', 'block-presets-manager' ),
			'name_admin_bar'     => __( 'Block Preset', 'block-presets-manager' ),
			'add_new'            => __( 'Add New', 'block-presets-manager' ),
			'add_new_item'       => __( 'Add New Block Preset', 'block-presets-manager' ),
			'new_item'           => __( 'New Block Preset', 'block-presets-manager' ),
			'edit_item'          => __( 'Edit Block Preset', 'block-presets-manager' ),
			'view_item'          => __( 'View Block Preset', 'block-presets-manager' ),
			'view_items'         => __( 'View Block Presets', 'block-presets-manager' ),
			'all_items'          => __( 'All Block Presets', 'block-presets-manager' ),
			'search_items'       => __( 'Search Block Presets', 'block-presets-manager' ),
			'not_found'          => __( 'No block presets found.', 'block-presets-manager' ),
			'not_found_in_trash' => __( 'No block presets found in Trash.', 'block-presets-manager' ),
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
			'name'                       => _x( 'Block Categories', 'Taxonomy General Name', 'block-presets-manager' ),
			'singular_name'              => _x( 'Block Category', 'Taxonomy Singular Name', 'block-presets-manager' ),
			'menu_name'                  => __( 'Categories', 'block-presets-manager' ),
			'all_items'                  => __( 'All Categories', 'block-presets-manager' ),
			'parent_item'                => __( 'Parent Category', 'block-presets-manager' ),
			'parent_item_colon'          => __( 'Parent Category:', 'block-presets-manager' ),
			'new_item_name'              => __( 'New Category Name', 'block-presets-manager' ),
			'add_new_item'               => __( 'Add New Category', 'block-presets-manager' ),
			'edit_item'                  => __( 'Edit Category', 'block-presets-manager' ),
			'update_item'                => __( 'Update Category', 'block-presets-manager' ),
			'view_item'                  => __( 'View Category', 'block-presets-manager' ),
			'separate_items_with_commas' => __( 'Separate categories with commas', 'block-presets-manager' ),
			'add_or_remove_items'        => __( 'Add or remove categories', 'block-presets-manager' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'block-presets-manager' ),
			'popular_items'              => __( 'Popular Categories', 'block-presets-manager' ),
			'search_items'               => __( 'Search Categories', 'block-presets-manager' ),
			'not_found'                  => __( 'Not Found', 'block-presets-manager' ),
			'no_terms'                   => __( 'No categories', 'block-presets-manager' ),
			'items_list'                 => __( 'Categories list', 'block-presets-manager' ),
			'items_list_navigation'      => __( 'Categories list navigation', 'block-presets-manager' ),
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
			'name'                       => _x( 'Block Tags', 'Taxonomy General Name', 'block-presets-manager' ),
			'singular_name'              => _x( 'Block Tag', 'Taxonomy Singular Name', 'block-presets-manager' ),
			'menu_name'                  => __( 'Tags', 'block-presets-manager' ),
			'all_items'                  => __( 'All Tags', 'block-presets-manager' ),
			'new_item_name'              => __( 'New Tag Name', 'block-presets-manager' ),
			'add_new_item'               => __( 'Add New Tag', 'block-presets-manager' ),
			'edit_item'                  => __( 'Edit Tag', 'block-presets-manager' ),
			'update_item'                => __( 'Update Tag', 'block-presets-manager' ),
			'view_item'                  => __( 'View Tag', 'block-presets-manager' ),
			'separate_items_with_commas' => __( 'Separate tags with commas', 'block-presets-manager' ),
			'add_or_remove_items'        => __( 'Add or remove tags', 'block-presets-manager' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'block-presets-manager' ),
			'popular_items'              => __( 'Popular Tags', 'block-presets-manager' ),
			'search_items'               => __( 'Search Tags', 'block-presets-manager' ),
			'not_found'                  => __( 'Not Found', 'block-presets-manager' ),
			'no_terms'                   => __( 'No tags', 'block-presets-manager' ),
			'items_list'                 => __( 'Tags list', 'block-presets-manager' ),
			'items_list_navigation'      => __( 'Tags list navigation', 'block-presets-manager' ),
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
	 * Register legacy 'block' post type for backward compatibility
	 * This matches the original theme implementation
	 */
	public function register_legacy_block_post_type() {
		$labels = array(
			'name'               => __( 'Block Presets (Legacy)', 'block-presets-manager' ),
			'singular_name'      => __( 'Block Preset', 'block-presets-manager' ),
			'menu_name'          => __( 'Legacy Blocks', 'block-presets-manager' ),
			'all_items'          => __( 'All Legacy Blocks', 'block-presets-manager' ),
			'edit_item'          => __( 'Edit Block Preset', 'block-presets-manager' ),
			'view_item'          => __( 'View Block Preset', 'block-presets-manager' ),
			'add_new_item'       => __( 'Add New Block', 'block-presets-manager' ),
			'add_new'            => __( 'Add New', 'block-presets-manager' ),
			'new_item'           => __( 'New Block Preset', 'block-presets-manager' ),
			'search_items'       => __( 'Search Block Presets', 'block-presets-manager' ),
			'not_found'          => __( 'No block presets found.', 'block-presets-manager' ),
			'not_found_in_trash' => __( 'No block presets found in Trash.', 'block-presets-manager' ),
		);

		$args = array(
			'labels'              => $labels,
			'description'         => __( 'Legacy block presets (for backward compatibility only).', 'block-presets-manager' ),
			'public'              => false,
			'publicly_queryable'  => false,
			'show_ui'             => true,
			'show_in_menu'        => false, // Hide from main menu since we have the new post type.
			'show_in_admin_bar'   => false,
			'show_in_rest'        => true,
			'capability_type'     => 'post',
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
				'custom-fields',
			),
			'exclude_from_search' => true,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'delete_with_user'    => false,
			'can_export'          => true,
		);

		register_post_type( 'block', $args );
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
					$menu[ $key ][0] = __( 'Block Presets', 'block-presets-manager' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					break;
				}
			}

			// Ensure submenu items are properly labeled
			if ( isset( $submenu['edit.php?post_type=bpmgr_block_preset'] ) ) {
				foreach ( $submenu['edit.php?post_type=bpmgr_block_preset'] as $key => $item ) {
					if ( 'edit.php?post_type=bpmgr_block_preset' === $item[2] ) {
						$submenu['edit.php?post_type=bpmgr_block_preset'][ $key ][0] = __( 'All Block Presets', 'block-presets-manager' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					} elseif ( 'post-new.php?post_type=bpmgr_block_preset' === $item[2] ) {
						$submenu['edit.php?post_type=bpmgr_block_preset'][ $key ][0] = __( 'Add New Preset', 'block-presets-manager' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					}
				}
			}
		}
	}
}
