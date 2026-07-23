<?php
namespace um_ext\um_optimize\integrations;

defined( 'ABSPATH' ) || exit;
if ( class_exists( 'um_ext\um_optimize\integrations\Init' ) ) {
	return;
}

/**
 * Integration with other extensions.
 *
 * Get an instance this way: UM()->Optimize()->integrations()
 *
 * @package um_ext\um_optimize\integrations
 */
class Init {


	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( defined( 'UM_ACTIVITY_VERSION' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/activity.php';
		}
		if ( defined( 'UM_GROUPS_VERSION' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/groups.php';
		}
		if ( defined( 'UM_REVIEWS_VERSION' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/reviews.php';
		}
		if ( defined( 'UM_USER_NOTES_VERSION' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/user_notes.php';
		}
		if ( defined( 'UM_USER_PHOTOS_VERSION' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/user_photos.php';
		}

		if ( is_plugin_active( 'um-notices/um-notices.php' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/notices.php';
		}
		if ( is_plugin_active( 'um-notifications/um-notifications.php' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/notifications.php';
		}
		if ( is_plugin_active( 'um-user-bookmarks/um-user-bookmarks.php' ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/integrations/user-bookmarks.php';
		}
	}

}
