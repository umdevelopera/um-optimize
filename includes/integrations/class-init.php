<?php
namespace um_ext\um_optimize\integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
			require_once um_optimize_path . 'includes/integrations/activity.php';
		}
		if ( defined( 'UM_GROUPS_VERSION' ) ) {
			require_once um_optimize_path . 'includes/integrations/groups.php';
		}
		if ( defined( 'UM_REVIEWS_VERSION' ) ) {
			require_once um_optimize_path . 'includes/integrations/reviews.php';
		}
		if ( defined( 'UM_USER_NOTES_VERSION' ) ) {
			require_once um_optimize_path . 'includes/integrations/user_notes.php';
		}
		if ( defined( 'UM_USER_PHOTOS_VERSION' ) ) {
			require_once um_optimize_path . 'includes/integrations/user_photos.php';
		}

		if ( is_plugin_active( 'um-notices/um-notices.php' ) ) {
			require_once um_optimize_path . 'includes/integrations/um-notices.php';
		}
		if ( is_plugin_active( 'um-user-bookmarks/um-user-bookmarks.php' ) ) {
			require_once um_optimize_path . 'includes/integrations/um-user-bookmarks.php';
		}
	}

}
