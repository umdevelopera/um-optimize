<?php
/**
 * Optimize the User Bookmarks extension.
 *
 * @package um_ext\um_optimize\integrations
 */

defined( 'ABSPATH' ) || exit;
if ( ! defined( 'UM_USER_BOOKMARKS_VERSION' ) ) {
	return;
}

/**
 * Add assets that should not be dequeued.
 *
 * @param array $not_dequeue An array of assets that should not be dequeued.
 * @return array
 */
function um_optimize_user_bookmarks_not_dequeue( $not_dequeue ) {
	if ( is_user_logged_in() && um_user( 'enable_bookmark' ) ) {
		if ( is_singular() ) {
			$post_types = (array) UM()->options()->get( 'um_user_bookmarks_post_types' );
			if ( in_array( get_post_type(), $post_types, true ) ) {
				array_push( $not_dequeue, 'um-user-bookmarks' );
			}
		} elseif ( is_archive() && UM()->options()->get( 'um_user_bookmarks_archive_page' ) ) {
			array_push( $not_dequeue, 'um-user-bookmarks' );
		}
	}
	return $not_dequeue;
}
add_filter( 'um_optimize_not_dequeue', 'um_optimize_user_bookmarks_not_dequeue', 10 );
