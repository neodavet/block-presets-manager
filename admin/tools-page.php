<?php
/**
 * Admin Tools Page Template
 *
 * This template is always `include`d from BPMGR_Admin::tools_page(), so its
 * top-level variables are local to that method call at runtime, not real globals.
 * phpcs analyzes included template files statically and cannot see that, hence the
 * blanket disable below for the "unprefixed global" sniff.
 *
 * @package OnceBlock
 * @since 1.0.0
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Handle form submissions
if (
	isset( $_POST['bpmgr_action'], $_POST['bpmgr_nonce'] )
	&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bpmgr_nonce'] ) ), 'bpmgr_tools_action' )
) {
	$bpmgr_action = sanitize_text_field( wp_unslash( $_POST['bpmgr_action'] ) );

	switch ( $bpmgr_action ) {
		case 'reset_usage_stats':
			if ( bpmgr_reset_usage_stats() ) {
				echo '<div class="notice notice-success"><p>' . esc_html( __( 'Usage statistics have been reset.', 'onceblock' ) ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html( __( 'Failed to reset usage statistics.', 'onceblock' ) ) . '</p></div>';
			}
			break;

		// Note: 'export_presets' is intentionally not handled here. It sends file-download
		// headers, which must happen before any admin page HTML is output; see
		// BPMGR_Admin::maybe_export_presets(), hooked to 'admin_init'.
	}
}

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<div class="bpmgr-tools-container">
		
		<!-- Data Management Tools -->
		<div class="bpmgr-tool-section">
			<h2><?php esc_html_e( 'Data Management', 'onceblock' ); ?></h2>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Export Block Presets', 'onceblock' ); ?></h3>
				<p><?php esc_html_e( 'Export all block presets and their settings to a JSON file for backup or migration purposes.', 'onceblock' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( 'bpmgr_tools_action', 'bpmgr_nonce' ); ?>
					<input type="hidden" name="bpmgr_action" value="export_presets">
					<button type="submit" class="button">
						<?php esc_html_e( 'Export Block Presets', 'onceblock' ); ?>
					</button>
				</form>
			</div>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Reset Usage Statistics', 'onceblock' ); ?></h3>
				<p><?php esc_html_e( 'Clear all usage statistics data. This will reset the usage counters for all block presets.', 'onceblock' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( 'bpmgr_tools_action', 'bpmgr_nonce' ); ?>
					<input type="hidden" name="bpmgr_action" value="reset_usage_stats">
					<button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all usage statistics? This action cannot be undone.', 'onceblock' ); ?>')">
						<?php esc_html_e( 'Reset Usage Statistics', 'onceblock' ); ?>
					</button>
				</form>
			</div>
		</div>
		
		<!-- System Information -->
		<div class="bpmgr-tool-section">
			<h2><?php esc_html_e( 'System Information', 'onceblock' ); ?></h2>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Plugin Status', 'onceblock' ); ?></h3>
				<table class="widefat">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Plugin Version:', 'onceblock' ); ?></strong></td>
							<td><?php echo esc_html( BPMGR_VERSION ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'WordPress Version:', 'onceblock' ); ?></strong></td>
							<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'ACF Plugin:', 'onceblock' ); ?></strong></td>
							<td><?php echo wp_kses( function_exists( 'acf_register_block' ) ? '<span class="bpmgr-success">✓ Active</span>' : '<span class="bpmgr-error">✗ Not found</span>', array( 'span' => array( 'class' => array() ) ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Block Presets Count:', 'onceblock' ); ?></strong></td>
							<td><?php echo esc_html( wp_count_posts( 'bpmgr_block_preset' )->publish ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- Theme Integration -->
		<div class="bpmgr-tool-section">
			<h2><?php esc_html_e( 'Theme Integration', 'onceblock' ); ?></h2>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'ACF Block Folders Status', 'onceblock' ); ?></h3>
				<?php
				$settings      = get_option( 'bpmgr_settings', array() );
				$block_folders = isset( $settings['block_folders'] ) ? $settings['block_folders'] : array();

				if ( empty( $block_folders ) ) {
					echo '<p class="bpmgr-warning">' . esc_html( __( 'No block folders configured.', 'onceblock' ) ) . '</p>';
				} else {
					echo '<ul>';
					foreach ( $block_folders as $folder ) {
						$full_path     = get_theme_file_path( '/' . ltrim( $folder, '/' ) . '/' );
						$exists        = file_exists( $full_path ) && is_dir( $full_path );
						$folder_status = $exists ? '<span class="bpmgr-success">✓</span>' : '<span class="bpmgr-error">✗</span>';
						echo '<li>' . wp_kses( $folder_status, array( 'span' => array( 'class' => array() ) ) ) . ' ' . esc_html( $folder ) . '</li>';
					}
					echo '</ul>';
				}
				?>
			</div>
		</div>
		
	</div>
</div>

