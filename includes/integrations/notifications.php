<?php
/**
 * Optimize the Real-time Notifications extension.
 *
 * @package um_ext\um_optimize\integrations
 */

defined( 'ABSPATH' ) || exit;
if ( ! defined( 'UM_NOTIFICATIONS_VERSION' ) ) {
	return;
}

/**
 * Add assets that should not be dequeued.
 *
 * @param array  $not_dequeue An array of assets that should not be dequeued.
 * @param string $ext         A type of assets: css or js.
 * @return array
 */
function um_optimize_notifications_not_dequeue( $not_dequeue, $ext ) {
	if ( is_user_logged_in() ) {
		$count = UM()->Notifications()->common()->notifications()->get_new_count();
		if ( $count['raw'] || UM()->options()->get( 'notification_icon_visibility' ) ) {
			array_push( $not_dequeue, 'um_notifications' );
		}
	}
	return $not_dequeue;
}
add_filter( 'um_optimize_not_dequeue', 'um_optimize_notifications_not_dequeue', 10, 2 );
