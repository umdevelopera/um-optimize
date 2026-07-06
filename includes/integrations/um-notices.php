<?php
/**
 * Optimize the Notices extension.
 *
 * @package um_ext\um_optimize\integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! defined( 'UM_NOTICES_VERSION' ) ) {
	return;
}

/**
 * Add assets that should not be dequeued.
 *
 * @param array  $not_dequeue An array of assets that should not be dequeued.
 * @param string $ext         A type of assets: css or js.
 * @return array
 */
function um_optimize_notices_not_dequeue( $not_dequeue, $ext ) {
	$notice_id = UM()->Notices()->frontend()->notices()->notice_id;
	if ( $notice_id ) {
		if ( 'css' === $ext ) {
			wp_enqueue_style( 'um_styles' );
		}
		array_push( $not_dequeue, 'um_notices' );
		array_push( $not_dequeue, 'um_styles' );
	}
	return $not_dequeue;
}
add_filter( 'um_optimize_not_dequeue', 'um_optimize_notices_not_dequeue', 10, 2 );
