<?php
/**
 * Admin Statistics Page Template
 *
 * This template is always `include`d from BPMGR_Admin::statistics_page(), so its
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

// Defense-in-depth: the menu registration already restricts this page to 'manage_options',
// but verify again in case this template is ever included from elsewhere.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'block-presets-manager' ) );
}

// Get overall statistics with caching
$total_presets  = wp_count_posts( 'bpmgr_block_preset' )->publish;
$total_usage    = bpmgr_get_total_usage_cached();
$most_used      = bpmgr_get_most_used_presets_cached();
$unused_presets = bpmgr_get_unused_presets_cached();
$recent_usage   = bpmgr_get_recent_usage_cached();

?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<div class="bpmgr-stats-container">
		
		<!-- Overview Cards -->
		<div class="bpmgr-stats-cards">
			<div class="bpmgr-stat-card">
				<h3><?php esc_html_e( 'Total Block Presets', 'block-presets-manager' ); ?></h3>
				<div class="bpmgr-stat-number"><?php echo esc_html( $total_presets ); ?></div>
			</div>
			
			<div class="bpmgr-stat-card">
				<h3><?php esc_html_e( 'Total Usage Count', 'block-presets-manager' ); ?></h3>
				<div class="bpmgr-stat-number"><?php echo esc_html( $total_usage ? $total_usage : 0 ); ?></div>
			</div>
			
			<div class="bpmgr-stat-card">
				<h3><?php esc_html_e( 'Unused Presets', 'block-presets-manager' ); ?></h3>
				<div class="bpmgr-stat-number"><?php echo esc_html( count( $unused_presets ) ); ?></div>
			</div>
			
			<div class="bpmgr-stat-card">
				<h3><?php esc_html_e( 'Average Usage', 'block-presets-manager' ); ?></h3>
				<div class="bpmgr-stat-number">
					<?php echo esc_html( $total_presets > 0 ? round( ( $total_usage ? $total_usage : 0 ) / $total_presets, 1 ) : 0 ); ?>
				</div>
			</div>
		</div>
		
		<div class="bpmgr-stats-grid">
			
			<!-- Most Used Presets -->
			<div class="bpmgr-stats-section">
				<h2><?php esc_html_e( 'Most Used Block Presets', 'block-presets-manager' ); ?></h2>
				
				<?php if ( ! empty( $most_used ) ) : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Block Preset', 'block-presets-manager' ); ?></th>
								<th><?php esc_html_e( 'Usage Count', 'block-presets-manager' ); ?></th>
								<th><?php esc_html_e( 'Actions', 'block-presets-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $most_used as $preset ) : ?>
							<tr>
								<td>
									<strong>
										<a href="<?php echo esc_url( get_edit_post_link( $preset->ID ) ); ?>">
											<?php echo esc_html( $preset->post_title ); ?>
										</a>
									</strong>
								</td>
								<td><?php echo esc_html( $preset->total_usage ? $preset->total_usage : 0 ); ?></td>
								<td>
									<a href="<?php echo esc_url( get_edit_post_link( $preset->ID ) ); ?>" class="button button-small">
										<?php esc_html_e( 'Edit', 'block-presets-manager' ); ?>
									</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No usage data available yet.', 'block-presets-manager' ); ?></p>
				<?php endif; ?>
			</div>
			
			<!-- Recent Activity -->
			<div class="bpmgr-stats-section">
				<h2><?php esc_html_e( 'Recent Usage Activity', 'block-presets-manager' ); ?></h2>
				
				<?php if ( ! empty( $recent_usage ) ) : ?>
					<table class="wp-list-table widefat fixed striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Block Preset', 'block-presets-manager' ); ?></th>
								<th><?php esc_html_e( 'Used On', 'block-presets-manager' ); ?></th>
								<th><?php esc_html_e( 'Last Used', 'block-presets-manager' ); ?></th>
								<th><?php esc_html_e( 'Count', 'block-presets-manager' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $recent_usage as $usage ) : ?>
							<tr>
								<td><?php echo esc_html( $usage->block_title ); ?></td>
								<td><?php echo esc_html( $usage->used_on ); ?></td>
								<td><?php echo esc_html( human_time_diff( strtotime( $usage->last_used ), strtotime( current_time( 'mysql' ) ) ) . ' ' . __( 'ago', 'block-presets-manager' ) ); ?></td>
								<td><?php echo esc_html( $usage->usage_count ); ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p><?php esc_html_e( 'No recent usage activity.', 'block-presets-manager' ); ?></p>
				<?php endif; ?>
			</div>
			
		</div>
		
		<!-- Unused Presets -->
		<?php if ( ! empty( $unused_presets ) ) : ?>
		<div class="bpmgr-stats-section bpmgr-full-width">
			<h2><?php esc_html_e( 'Unused Block Presets', 'block-presets-manager' ); ?></h2>
			<p><?php esc_html_e( 'These block presets have never been used. Consider reviewing them for relevance.', 'block-presets-manager' ); ?></p>
			
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Block Preset', 'block-presets-manager' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'block-presets-manager' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $unused_presets as $preset ) : ?>
					<tr>
						<td>
							<strong>
								<a href="<?php echo esc_url( get_edit_post_link( $preset->ID ) ); ?>">
									<?php echo esc_html( $preset->post_title ); ?>
								</a>
							</strong>
						</td>
						<td>
							<a href="<?php echo esc_url( get_edit_post_link( $preset->ID ) ); ?>" class="button button-small">
								<?php esc_html_e( 'Edit', 'block-presets-manager' ); ?>
							</a>
							<a href="<?php echo esc_url( get_permalink( $preset->ID ) ); ?>" class="button button-small" target="_blank">
								<?php esc_html_e( 'Preview', 'block-presets-manager' ); ?>
							</a>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>
		
		<!-- Usage by Month Chart -->
		<div class="bpmgr-stats-section bpmgr-full-width">
			<h2><?php esc_html_e( 'Usage Trends', 'block-presets-manager' ); ?></h2>
			
			<?php
			// Get usage by month for the last 12 months
			$monthly_usage = bpmgr_get_monthly_usage_cached();

			if ( ! empty( $monthly_usage ) ) :
				?>
				<div class="bpmgr-chart-container">
					<canvas id="bpmgr-usage-chart" width="400" height="200"></canvas>
				</div>
				
				<script>
				// Simple chart implementation (you might want to use Chart.js or similar)
				document.addEventListener('DOMContentLoaded', function() {
					const canvas = document.getElementById('bpmgr-usage-chart');
					const ctx = canvas.getContext('2d');
					
					// Chart data
					const data = <?php echo wp_json_encode( $monthly_usage, JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
					
					// Draw simple bar chart
					const padding = 40;
					const chartWidth = canvas.width - (padding * 2);
					const chartHeight = canvas.height - (padding * 2);
					const barWidth = chartWidth / data.length;
					const maxValue = Math.max(...data.map(d => parseInt(d.total_usage)));
					
					// Clear canvas
					ctx.clearRect(0, 0, canvas.width, canvas.height);
					
					// Draw bars
					data.forEach((item, index) => {
						const barHeight = (parseInt(item.total_usage) / maxValue) * chartHeight;
						const x = padding + (index * barWidth);
						const y = canvas.height - padding - barHeight;
						
						ctx.fillStyle = '#0073aa';
						ctx.fillRect(x + 5, y, barWidth - 10, barHeight);
						
						// Draw labels
						ctx.fillStyle = '#333';
						ctx.font = '12px Arial';
						ctx.textAlign = 'center';
						ctx.fillText(item.month, x + (barWidth / 2), canvas.height - 10);
						ctx.fillText(item.total_usage, x + (barWidth / 2), y - 5);
					});
				});
				</script>
			<?php else : ?>
				<p><?php esc_html_e( 'No usage trend data available yet.', 'block-presets-manager' ); ?></p>
			<?php endif; ?>
		</div>
		
	</div>
</div>

<?php
/**
 * Helper functions for statistics with caching
 */

/**
 * Get total usage count with caching
 */
function bpmgr_get_total_usage_cached() {
	$cache_key   = 'bpmgr_total_usage';
	$total_usage = wp_cache_get( $cache_key, 'bpmgr_statistics' );

	if ( false === $total_usage ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'bpmgr_block_usage';
		// Only the table name ($wpdb->prefix / $wpdb->posts) is interpolated, never user input; $wpdb->prepare() cannot parameterize identifiers.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- reads the plugin's own table (identifier only is interpolated); the result is cached with wp_cache_set() below.
		$total_usage = $wpdb->get_var( "SELECT SUM(usage_count) FROM {$table_name}" );

		// Cache for 5 minutes
		wp_cache_set( $cache_key, $total_usage, 'bpmgr_statistics', 300 );
	}

	return $total_usage;
}

/**
 * Get most used presets with caching
 */
function bpmgr_get_most_used_presets_cached() {
	$cache_key = 'bpmgr_most_used_presets';
	$most_used = wp_cache_get( $cache_key, 'bpmgr_statistics' );

	if ( false === $most_used ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'bpmgr_block_usage';
		// Only the table name ($wpdb->prefix / $wpdb->posts) is interpolated, never user input; $wpdb->prepare() cannot parameterize identifiers.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- reads the plugin's own table (identifiers only are interpolated); the result is cached with wp_cache_set() below.
		$most_used = $wpdb->get_results(
			"
            SELECT p.ID, p.post_title, SUM(u.usage_count) as total_usage 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$table_name} u ON p.ID = u.block_id 
            WHERE p.post_type = 'bpmgr_block_preset' AND p.post_status = 'publish'
            GROUP BY p.ID 
            ORDER BY total_usage DESC 
            LIMIT 10
        "
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery

		// Cache for 10 minutes
		wp_cache_set( $cache_key, $most_used, 'bpmgr_statistics', 600 );
	}

	return $most_used;
}

/**
 * Get unused presets with caching
 */
function bpmgr_get_unused_presets_cached() {
	$cache_key      = 'bpmgr_unused_presets';
	$unused_presets = wp_cache_get( $cache_key, 'bpmgr_statistics' );

	if ( false === $unused_presets ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'bpmgr_block_usage';
		// Only the table name ($wpdb->prefix / $wpdb->posts) is interpolated, never user input; $wpdb->prepare() cannot parameterize identifiers.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- reads the plugin's own table (identifiers only are interpolated); the result is cached with wp_cache_set() below.
		$unused_presets = $wpdb->get_results(
			"
            SELECT p.ID, p.post_title 
            FROM {$wpdb->posts} p 
            LEFT JOIN {$table_name} u ON p.ID = u.block_id 
            WHERE p.post_type = 'bpmgr_block_preset' 
            AND p.post_status = 'publish' 
            AND u.block_id IS NULL 
            ORDER BY p.post_title ASC
        "
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery

		// Cache for 15 minutes
		wp_cache_set( $cache_key, $unused_presets, 'bpmgr_statistics', 900 );
	}

	return $unused_presets;
}

/**
 * Get recent usage with caching
 */
function bpmgr_get_recent_usage_cached() {
	$cache_key    = 'bpmgr_recent_usage';
	$recent_usage = wp_cache_get( $cache_key, 'bpmgr_statistics' );

	if ( false === $recent_usage ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'bpmgr_block_usage';
		// Only the table name ($wpdb->prefix / $wpdb->posts) is interpolated, never user input; $wpdb->prepare() cannot parameterize identifiers.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- reads the plugin's own table (identifiers only are interpolated); the result is cached with wp_cache_set() below.
		$recent_usage = $wpdb->get_results(
			"
            SELECT p.post_title as block_title, p2.post_title as used_on, u.last_used, u.usage_count
            FROM {$table_name} u
            LEFT JOIN {$wpdb->posts} p ON u.block_id = p.ID
            LEFT JOIN {$wpdb->posts} p2 ON u.post_id = p2.ID
            ORDER BY u.last_used DESC
            LIMIT 20
        "
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery

		// Cache for 5 minutes
		wp_cache_set( $cache_key, $recent_usage, 'bpmgr_statistics', 300 );
	}

	return $recent_usage;
}

/**
 * Get monthly usage trends with caching
 */
function bpmgr_get_monthly_usage_cached() {
	$cache_key     = 'bpmgr_monthly_usage';
	$monthly_usage = wp_cache_get( $cache_key, 'bpmgr_statistics' );

	if ( false === $monthly_usage ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'bpmgr_block_usage';
		// Only the table name ($wpdb->prefix / $wpdb->posts) is interpolated, never user input; $wpdb->prepare() cannot parameterize identifiers.
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery -- reads the plugin's own table (identifiers only are interpolated); the result is cached with wp_cache_set() below.
		$monthly_usage = $wpdb->get_results(
			"
            SELECT DATE_FORMAT(last_used, '%Y-%m') as month, SUM(usage_count) as total_usage
            FROM {$table_name}
            WHERE last_used >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(last_used, '%Y-%m')
            ORDER BY month ASC
        "
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery

		// Cache for 1 hour
		wp_cache_set( $cache_key, $monthly_usage, 'bpmgr_statistics', 3600 );
	}

	return $monthly_usage;
}

/**
 * Clear statistics cache when usage data is updated
 */
function bpmgr_clear_statistics_cache() {
	wp_cache_delete( 'bpmgr_total_usage', 'bpmgr_statistics' );
	wp_cache_delete( 'bpmgr_most_used_presets', 'bpmgr_statistics' );
	wp_cache_delete( 'bpmgr_unused_presets', 'bpmgr_statistics' );
	wp_cache_delete( 'bpmgr_recent_usage', 'bpmgr_statistics' );
	wp_cache_delete( 'bpmgr_monthly_usage', 'bpmgr_statistics' );
}
?>
