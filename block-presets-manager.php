<?php
/**
 * Plugin Name: Block Presets Manager
 * Plugin URI: https://neodavet.github.io/davetportfolio/
 * Description: A powerful plugin for creating and managing reusable Gutenberg block presets. Allows you to create custom block templates and ACF blocks that can be reused across your website.
 * Version: 1.0.2
 * Author: davet86
 * Author URI: https://neodavet.github.io/davetportfolio/
 * Text Domain: block-presets-manager
 * Domain Path: /languages
 * Requires at least: 5.9
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Copyright (C) 2026 David Tavares
 *
 * @package Block_Presets_Manager
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'BPMGR_VERSION', '1.0.2' );
define( 'BPMGR_PLUGIN_FILE', __FILE__ );
define( 'BPMGR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BPMGR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BPMGR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'BPMGR_TEXT_DOMAIN', 'block-presets-manager' );

/**
 * Main Plugin Class
 */
class Block_Presets_Manager {

	/**
	 * Plugin instance
	 *
	 * @var Block_Presets_Manager
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
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
		$this->init_hooks();
		$this->load_dependencies();

		// Ensure core components are instantiated before 'init' fires,
		// so their own 'init' callbacks (e.g., CPT registration) are hooked in time.
		if ( class_exists( 'BPMGR_Post_Types' ) ) {
			BPMGR_Post_Types::get_instance();
		}
		if ( class_exists( 'BPMGR_Gutenberg_Blocks' ) ) {
			BPMGR_Gutenberg_Blocks::get_instance();
		}
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Initialize plugin
		add_action( 'init', array( $this, 'init' ) );

		// Admin hooks
		// Note: the admin menu, settings registration, and settings sanitization are all
		// owned by BPMGR_Admin (see includes/class-bpmgr-admin.php) - registering them here too
		// used to create a duplicate, conflicting registration of the 'bpmgr_settings' option.
		if ( is_admin() ) {
			// Register privacy policy content
			add_action( 'admin_init', array( $this, 'add_privacy_policy_content' ) );
		}
	}

	/**
	 * Load plugin dependencies
	 */
	private function load_dependencies() {
		require_once BPMGR_PLUGIN_DIR . 'includes/class-bpmgr-post-types.php';
		require_once BPMGR_PLUGIN_DIR . 'includes/class-bpmgr-acf-blocks.php';
		require_once BPMGR_PLUGIN_DIR . 'includes/class-bpmgr-helper-functions.php';
		require_once BPMGR_PLUGIN_DIR . 'includes/bpmgr-tools-functions.php';
		require_once BPMGR_PLUGIN_DIR . 'includes/class-bpmgr-admin.php';
		require_once BPMGR_PLUGIN_DIR . 'includes/class-bpmgr-gutenberg-blocks.php';
	}

	/**
	 * Initialize plugin
	 */
	public function init() {
		// Log that plugin is initializing (no-op unless debug logging is enabled).
		BPMGR_Helper_Functions::log( 'Initializing plugin components' );

		// Initialize components
		BPMGR_Post_Types::get_instance();
		BPMGR_ACF_Blocks::get_instance();
		BPMGR_Helper_Functions::get_instance();
		BPMGR_Gutenberg_Blocks::get_instance();

		if ( is_admin() ) {
			BPMGR_Admin::get_instance();
		}

		// Add admin notice for successful activation
		if ( is_admin() && get_transient( 'bpmgr_activation_notice' ) ) {
			add_action( 'admin_notices', array( $this, 'activation_notice' ) );
			delete_transient( 'bpmgr_activation_notice' );
		}

		// Log whether the post type was registered (no-op unless debug logging is enabled).
		add_action(
			'wp_loaded',
			function () {
				if ( ! BPMGR_Helper_Functions::is_debug_enabled() ) {
					return;
				}
				$exists = post_type_exists( 'bpmgr_block_preset' );
				BPMGR_Helper_Functions::log( 'Post type bpmgr_block_preset exists: ' . ( $exists ? 'YES' : 'NO' ) );
				if ( $exists ) {
					$post_type_obj = get_post_type_object( 'bpmgr_block_preset' );
					BPMGR_Helper_Functions::log( $post_type_obj->labels );
				}
			}
		);
	}

	/**
	 * Add privacy policy content describing data handling
	 */
	public function add_privacy_policy_content() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			$content  = '<p>' . esc_html__( 'Block Presets Manager stores minimal data locally to provide its functionality.', 'block-presets-manager' ) . '</p>';
			$content .= '<p>' . esc_html__( 'Specifically, it creates a local database table to track where a block preset is used: block ID, post ID, usage count, and last used date. No personal data is collected by this feature, and no data is transmitted to third parties.', 'block-presets-manager' ) . '</p>';
			$content .= '<p>' . esc_html__( 'If you uninstall the plugin from the Plugins screen, all plugin data (including this usage table, options, and related metadata) will be removed.', 'block-presets-manager' ) . '</p>';

			wp_add_privacy_policy_content(
				__( 'Block Presets Manager', 'block-presets-manager' ),
				wp_kses_post( $content )
			);
		}
	}

	/**
	 * Plugin activation
	 */
	public function activate() {
		// The post types are normally registered on 'init', which has already run by the time the
		// activation hook fires. Register them now so the flushed rewrite rules include their URLs;
		// otherwise single preset pages return 404 until the rules are flushed again.
		BPMGR_Post_Types::get_instance()->register_post_types();
		flush_rewrite_rules();

		// Set default options
		if ( ! get_option( 'bpmgr_settings' ) ) {
			$default_settings = array(
				'enable_acf_blocks'       => false, // Disabled by default - purely native WordPress.
				'enable_block_presets'    => true,
				'enable_legacy_post_type' => true, // Enable for backward compatibility.
				'block_folders'           => array(
					'general/acf-blocks',
					'public/acf-blocks',
					'login-register/acf-blocks',
					'members/acf-blocks',
				),
			);
			update_option( 'bpmgr_settings', $default_settings );
		}

		// Create custom tables if needed
		$this->create_custom_tables();

		// Set activation notice
		set_transient( 'bpmgr_activation_notice', true, 30 );
	}

	/**
	 * Plugin deactivation
	 */
	public function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Create custom tables
	 */
	private function create_custom_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Table name is built from $wpdb->prefix (not user input), so it is safe to interpolate;
		// $wpdb->prepare() does not support table/column identifiers as placeholders.
		$table_name = $wpdb->prefix . 'bpmgr_block_usage';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            block_id bigint(20) NOT NULL,
            post_id bigint(20) NOT NULL,
            usage_count int(11) DEFAULT 0,
            last_used datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY block_id (block_id),
            KEY post_id (post_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Display activation notice
	 */
	public function activation_notice() {
		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<strong><?php esc_html_e( 'Block Presets Manager activated successfully!', 'block-presets-manager' ); ?></strong>
				<?php esc_html_e( 'You can now manage your block presets from the', 'block-presets-manager' ); ?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bpmgr_block_preset' ) ); ?>"><strong><?php esc_html_e( 'Block Presets', 'block-presets-manager' ); ?></strong></a>
				<?php esc_html_e( 'menu.', 'block-presets-manager' ); ?>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bpmgr_block_preset&page=bpmgr-settings' ) ); ?>"><?php esc_html_e( 'Configure settings', 'block-presets-manager' ); ?></a>
			</p>
		</div>
		<?php
	}

	/**
	 * Static entry point for the activation hook
	 *
	 * Must be reachable without waiting for 'plugins_loaded': by the time WordPress fires the
	 * "activate_{$plugin}" hook during activation, 'plugins_loaded' has already completed for
	 * that request, so a callback registered only inside the plugins_loaded-deferred singleton
	 * would never run. See the top-level register_activation_hook() call below.
	 */
	public static function run_activation() {
		self::get_instance()->activate();
	}

	/**
	 * Static entry point for the deactivation hook
	 *
	 * Same reasoning as run_activation() above.
	 */
	public static function run_deactivation() {
		self::get_instance()->deactivate();
	}
}

// Activation and deactivation hooks must be registered unconditionally, every time this file
// loads - not deferred behind 'plugins_loaded' - otherwise WordPress won't see them in time
// when it fires the activation/deactivation hooks.
register_activation_hook( BPMGR_PLUGIN_FILE, array( 'Block_Presets_Manager', 'run_activation' ) );
register_deactivation_hook( BPMGR_PLUGIN_FILE, array( 'Block_Presets_Manager', 'run_deactivation' ) );

// Start the plugin after all plugins are loaded
add_action(
	'plugins_loaded',
	static function () {
		Block_Presets_Manager::get_instance();
	},
	10
);
