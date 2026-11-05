<?php
/**
 * Admin Tools Page Template
 *
 * This template is always `include`d from BPMGR_Admin::tools_page(), so its
 * top-level variables are local to that method call at runtime, not real globals.
 * phpcs analyzes included template files statically and cannot see that, hence the
 * blanket disable below for the "unprefixed global" sniff.
 *
 * @package Block_Presets_Manager
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
		case 'migrate_old_blocks':
			$migrated = bpmgr_migrate_old_blocks();
			if ( false !== $migrated ) {
				echo '<div class="notice notice-success"><p>' . esc_html(
					sprintf(
						/* translators: %d: Number of migrated block presets. */
						_n( 'Successfully migrated %d block preset from old format.', 'Successfully migrated %d block presets from old format.', $migrated, 'block-presets-manager' ),
						$migrated
					)
				) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html( __( 'Migration failed. Please check error logs.', 'block-presets-manager' ) ) . '</p></div>';
			}
			break;

		case 'reset_usage_stats':
			if ( bpmgr_reset_usage_stats() ) {
				echo '<div class="notice notice-success"><p>' . esc_html( __( 'Usage statistics have been reset.', 'block-presets-manager' ) ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html( __( 'Failed to reset usage statistics.', 'block-presets-manager' ) ) . '</p></div>';
			}
			break;

		// Note: 'export_presets' is intentionally not handled here. It sends file-download
		// headers, which must happen before any admin page HTML is output; see
		// BPMGR_Admin::maybe_export_presets(), hooked to 'admin_init'.
	}
}

// Check for old block posts
$old_blocks = get_posts(
	array(
		'post_type'      => 'block',
		'posts_per_page' => 1,
		'post_status'    => 'any',
	)
);

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<div class="bpmgr-tools-container">
		
		<!-- Migration Tool -->
		<div class="bpmgr-tool-section">
			<h2><?php esc_html_e( 'Migration Tools', 'block-presets-manager' ); ?></h2>
			
			<?php if ( ! empty( $old_blocks ) ) : ?>
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Migrate Old Block Presets', 'block-presets-manager' ); ?></h3>
				<p><?php esc_html_e( 'Found block presets using the old "block" post type. Click below to migrate them to the new format.', 'block-presets-manager' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( 'bpmgr_tools_action', 'bpmgr_nonce' ); ?>
					<input type="hidden" name="bpmgr_action" value="migrate_old_blocks">
					<button type="submit" class="button button-primary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to migrate old block presets? This action cannot be undone.', 'block-presets-manager' ); ?>')">
						<?php esc_html_e( 'Migrate Old Blocks', 'block-presets-manager' ); ?>
					</button>
				</form>
			</div>
			<?php else : ?>
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Migration Status', 'block-presets-manager' ); ?></h3>
				<p class="bpmgr-success"><?php esc_html_e( '✓ No old block presets found. Migration is complete or not needed.', 'block-presets-manager' ); ?></p>
			</div>
			<?php endif; ?>
		</div>
		
		<!-- Data Management Tools -->
		<div class="bpmgr-tool-section">
			<h2><?php esc_html_e( 'Data Management', 'block-presets-manager' ); ?></h2>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Export Block Presets', 'block-presets-manager' ); ?></h3>
				<p><?php esc_html_e( 'Export all block presets and their settings to a JSON file for backup or migration purposes.', 'block-presets-manager' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( 'bpmgr_tools_action', 'bpmgr_nonce' ); ?>
					<input type="hidden" name="bpmgr_action" value="export_presets">
					<button type="submit" class="button">
						<?php esc_html_e( 'Export Block Presets', 'block-presets-manager' ); ?>
					</button>
				</form>
			</div>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Reset Usage Statistics', 'block-presets-manager' ); ?></h3>
				<p><?php esc_html_e( 'Clear all usage statistics data. This will reset the usage counters for all block presets.', 'block-presets-manager' ); ?></p>
				<form method="post" action="">
					<?php wp_nonce_field( 'bpmgr_tools_action', 'bpmgr_nonce' ); ?>
					<input type="hidden" name="bpmgr_action" value="reset_usage_stats">
					<button type="submit" class="button button-secondary" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to reset all usage statistics? This action cannot be undone.', 'block-presets-manager' ); ?>')">
						<?php esc_html_e( 'Reset Usage Statistics', 'block-presets-manager' ); ?>
					</button>
				</form>
			</div>
		</div>
		
		<!-- System Information -->
		<div class="bpmgr-tool-section">
			<h2><?php esc_html_e( 'System Information', 'block-presets-manager' ); ?></h2>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'Plugin Status', 'block-presets-manager' ); ?></h3>
				<table class="widefat">
					<tbody>
						<tr>
							<td><strong><?php esc_html_e( 'Plugin Version:', 'block-presets-manager' ); ?></strong></td>
							<td><?php echo esc_html( BPMGR_VERSION ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'WordPress Version:', 'block-presets-manager' ); ?></strong></td>
							<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'ACF Plugin:', 'block-presets-manager' ); ?></strong></td>
							<td><?php echo wp_kses( function_exists( 'acf_register_block' ) ? '<span class="bpmgr-success">✓ Active</span>' : '<span class="bpmgr-error">✗ Not found</span>', array( 'span' => array( 'class' => array() ) ) ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Block Presets Count:', 'block-presets-manager' ); ?></strong></td>
							<td><?php echo esc_html( wp_count_posts( 'bpmgr_block_preset' )->publish ); ?></td>
						</tr>
						<tr>
							<td><strong><?php esc_html_e( 'Old Blocks Count:', 'block-presets-manager' ); ?></strong></td>
							<td><?php echo esc_html( wp_count_posts( 'block' ) ? wp_count_posts( 'block' )->publish : 0 ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		
		<!-- Theme Integration -->
		<div class="bpmgr-tool-section">
			<h2><?php esc_html_e( 'Theme Integration', 'block-presets-manager' ); ?></h2>
			
			<div class="bpmgr-tool-card">
				<h3><?php esc_html_e( 'ACF Block Folders Status', 'block-presets-manager' ); ?></h3>
				<?php
				$settings      = get_option( 'bpmgr_settings', array() );
				$block_folders = isset( $settings['block_folders'] ) ? $settings['block_folders'] : array();

				if ( empty( $block_folders ) ) {
					echo '<p class="bpmgr-warning">' . esc_html( __( 'No block folders configured.', 'block-presets-manager' ) ) . '</p>';
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

