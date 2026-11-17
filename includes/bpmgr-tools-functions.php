<?php
/**
 * Helper functions for the Tools admin screen (usage reset, export)
 *
 * Loaded unconditionally alongside the plugin's other dependencies (not only when the
 * Tools page itself renders) so bpmgr_export_presets() is available to BPMGR_Admin's
 * admin_init handler, which must run - and may need to send file-download headers -
 * before the Tools page template ever outputs any HTML.
 *
 * @package OnceBlock
 * @since 1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Truncate the block usage statistics table
 *
 * @return bool True on success, false on failure.
 */
function bpmgr_reset_usage_stats() {
	global $wpdb;
	// Table name is built from $wpdb->prefix (not user input), so it is safe to interpolate;
	// $wpdb->prepare() does not support table/column identifiers as placeholders.
	$table_name = $wpdb->prefix . 'bpmgr_block_usage';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table_name is $wpdb->prefix plus a literal, never user input.
	return false !== $wpdb->query( "TRUNCATE TABLE $table_name" );
}

/**
 * Build an exportable array of all block presets and their settings
 *
 * @return array|false Export data, or false if there are no presets to export.
 */
function bpmgr_export_presets() {
	$presets = get_posts(
		array(
			'post_type'      => 'bpmgr_block_preset',
			'posts_per_page' => -1,
			'post_status'    => 'any',
		)
	);

	if ( empty( $presets ) ) {
		return false;
	}

	$export_data = array(
		'version'     => BPMGR_VERSION,
		'export_date' => current_time( 'mysql' ),
		'presets'     => array(),
	);

	foreach ( $presets as $preset ) {
		$preset_data = array(
			'title'      => $preset->post_title,
			'content'    => $preset->post_content,
			'excerpt'    => $preset->post_excerpt,
			'status'     => $preset->post_status,
			'meta'       => get_post_meta( $preset->ID ),
			'categories' => wp_get_post_terms( $preset->ID, 'bpmgr_block_category' ),
			'tags'       => wp_get_post_terms( $preset->ID, 'bpmgr_block_tag' ),
		);

		$export_data['presets'][] = $preset_data;
	}

	return $export_data;
}
