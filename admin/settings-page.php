<?php
/**
 * Admin Settings Page Template
 *
 * @package Block_Presets_Manager
 * @since 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<?php settings_errors(); ?>
	
	<div class="bpmgr-admin-container">
		<div class="bpmgr-admin-main">
			<form method="post" action="options.php">
				<?php
				settings_fields( 'bpmgr_settings_group' );
				do_settings_sections( 'block-presets-manager' );
				submit_button();
				?>
			</form>
		</div>
		
		<div class="bpmgr-admin-sidebar">
			<div class="bpmgr-widget">
				<h3><?php esc_html_e( 'Plugin Information', 'block-presets-manager' ); ?></h3>
				<ul>
					<li><strong><?php esc_html_e( 'Version:', 'block-presets-manager' ); ?></strong> <?php echo esc_html( BPMGR_VERSION ); ?></li>
					<li><strong><?php esc_html_e( 'Post Type:', 'block-presets-manager' ); ?></strong> bpmgr_block_preset</li>
					<li><strong><?php esc_html_e( 'Shortcode:', 'block-presets-manager' ); ?></strong> [bpmgr_block id="123"]</li>
				</ul>
			</div>
			
			<div class="bpmgr-widget">
				<h3><?php esc_html_e( 'Quick Actions', 'block-presets-manager' ); ?></h3>
				<p>
					<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=bpmgr_block_preset' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Create New Block Preset', 'block-presets-manager' ); ?>
					</a>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bpmgr_block_preset' ) ); ?>" class="button">
						<?php esc_html_e( 'Manage Block Presets', 'block-presets-manager' ); ?>
					</a>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=bpmgr_block_preset&page=bpmgr-statistics' ) ); ?>" class="button">
						<?php esc_html_e( 'View Statistics', 'block-presets-manager' ); ?>
					</a>
				</p>
			</div>
			
			<div class="bpmgr-widget">
				<h3><?php esc_html_e( 'Documentation', 'block-presets-manager' ); ?></h3>
				<p><?php esc_html_e( 'Use these functions in your theme:', 'block-presets-manager' ); ?></p>
				<code>bpmgr_render_block_preset(123);</code><br>
				<code>do_cpt_block(123); // Legacy</code><br><br>
				
				<p><?php esc_html_e( 'Use shortcodes in content:', 'block-presets-manager' ); ?></p>
				<code>[bpmgr_block id="123"]</code><br>
				<code>[block_preset id="123"]</code>
			</div>
		</div>
	</div>
</div>
