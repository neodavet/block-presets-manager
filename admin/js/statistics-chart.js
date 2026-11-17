/**
 * Usage Trends bar chart for the Statistics screen.
 *
 * Reads its data from window.bpmgrUsageData, which is attached with
 * wp_add_inline_script() in admin/statistics-page.php.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		var canvas = document.getElementById( 'bpmgr-usage-chart' );
		var data = window.bpmgrUsageData;

		if ( ! canvas || ! canvas.getContext || ! Array.isArray( data ) || ! data.length ) {
			return;
		}

		var ctx = canvas.getContext( '2d' );
		var padding = 40;
		var chartWidth = canvas.width - ( padding * 2 );
		var chartHeight = canvas.height - ( padding * 2 );
		var barWidth = chartWidth / data.length;
		var maxValue = Math.max.apply( null, data.map( function ( d ) {
			return parseInt( d.total_usage, 10 );
		} ) ) || 1;

		ctx.clearRect( 0, 0, canvas.width, canvas.height );

		data.forEach( function ( item, index ) {
			var barHeight = ( parseInt( item.total_usage, 10 ) / maxValue ) * chartHeight;
			var x = padding + ( index * barWidth );
			var y = canvas.height - padding - barHeight;

			ctx.fillStyle = '#0073aa';
			ctx.fillRect( x + 5, y, barWidth - 10, barHeight );

			ctx.fillStyle = '#333';
			ctx.font = '12px Arial';
			ctx.textAlign = 'center';
			ctx.fillText( item.month, x + ( barWidth / 2 ), canvas.height - 10 );
			ctx.fillText( item.total_usage, x + ( barWidth / 2 ), y - 5 );
		} );
	} );
}() );
