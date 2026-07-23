<?php
/**
 * Speed up profiles.
 *
 * @package um_ext\um_optimize\frontend
 */

namespace um_ext\um_optimize\frontend;

defined( 'ABSPATH' ) || exit;
if ( class_exists( 'um_ext\um_optimize\frontend\Profile' ) ) {
	return;
}

/**
 * Features to speed up profiles.
 *
 * Get an instance this way: UM()->Optimize()->frontend()->profile()
 *
 * @package um_ext\um_optimize\frontend
 */
class Profile {


	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Profile Menu caching.
		if ( UM()->options()->get( 'um_optimize_profile_tabs' ) ) {
			add_filter( 'um_profile_tabs', array( $this, 'um_profile_tabs' ), 10001, 1 );
			add_filter( 'um_user_profile_tabs', array( $this, 'um_profile_tabs_active' ), 10000001, 1 );
		}

		// Profile tab AJAX loading.
		if ( UM()->options()->get( 'profile_menu' ) && UM()->options()->get( 'um_optimize_profile_ajax' ) ) {
			add_action( 'um_profile_menu', array( $this, 'load_assets' ), 20 );
		}
	}

	/**
	 * Profile tab AJAX loading.
	 *
	 * Hooked: um_profile_menu - 20
	 *
	 * @param array $args Profile data.
	 */
	public function load_assets( $args ) {

		$enqueue = UM()->frontend()->enqueue();
		$suffix  = $enqueue::get_suffix();

		// Profile tab AJAX loading script.
		wp_register_script(
				'um-optimize-profile-tabs',
				UM_OPTIMIZE_URL . 'assets/js/um-optimize-profile-tabs' . $suffix . '.js',
				array( 'jquery' ),
				UM_OPTIMIZE_VERSION,
				array(
					'in_footer' => true,
					'strategy'  => 'async',
				)
		);
		wp_enqueue_script( 'um-optimize-profile-tabs' );

		// Load assets and templates for extensions.
		if ( is_plugin_active( 'um-bbpress/um-bbpress.php' ) ) {
			wp_enqueue_script( 'um_bbpress' );
			wp_enqueue_style( 'um_bbpress' );
		}
		if ( is_plugin_active( 'um-groups/um-groups.php' ) ) {
			wp_enqueue_script( 'um_groups' );
			wp_enqueue_style( 'um_groups' );
		}
		if ( is_plugin_active( 'um-social-activity/um-social-activity.php' ) ) {
			if ( UM()->is_new_ui() ) {
				UM()->Activity()->frontend()->enqueue()->enqueue_scripts();
			} else {
				UM()->Activity_API()->enqueue()->enqueue_scripts();
			}
		}
		if ( is_plugin_active( 'um-user-notes/um-user-notes.php' ) ) {
			UM()->User_Notes()->frontend()->enqueue()->enqueue_scripts();
		}
		if ( is_plugin_active( 'um-user-photos/um-user-photos.php' ) ) {
			if ( um_is_myprofile() ) {
				UM()->User_Photos()->common()->shortcodes_v2()->modal_template();
			}
		}
	}

	/**
	 * Cache profile tabs.
	 *
	 * Hooked: um_profile_tabs - 10001
	 *
	 * @param array $tabs Profile tabs.
	 * @return array
	 */
	public function um_profile_tabs( $tabs ) {
		wp_cache_add( 'profile_tabs', $tabs, 'ultimate-member' );
		remove_all_filters( 'um_profile_tabs' );
		add_filter( 'um_profile_tabs', array( $this, 'um_profile_tabs_cache' ), 10, 1 );
		return $tabs;
	}

	/**
	 * Apply cacheded profile tabs.
	 *
	 * Hooked: um_profile_tabs - 10
	 *
	 * @param array $tabs Profile tabs.
	 * @return array
	 */
	public function um_profile_tabs_cache( $tabs ) {
		$cache = wp_cache_get( 'profile_tabs', 'ultimate-member' );
		if ( $cache && is_array( $cache ) ) {
			$tabs = array_merge( $tabs, $cache );
		}
		return $tabs;
	}

	/**
	 * Cache active profile tabs.
	 *
	 * Hooked: um_user_profile_tabs - 10000001
	 *
	 * @param array $tabs Profile tabs.
	 * @return array
	 */
	public function um_profile_tabs_active( $tabs ) {
		wp_cache_add( 'profile_tabs_active', $tabs, 'ultimate-member' );
		remove_all_filters( 'um_user_profile_tabs' );
		add_filter( 'um_user_profile_tabs', array( $this, 'um_profile_tabs_active_cache' ), 10, 1 );
		return $tabs;
	}

	/**
	 * Apply cacheded active profile tabs.
	 *
	 * Hooked: um_user_profile_tabs - 10
	 *
	 * @param array $tabs Profile tabs.
	 * @return array
	 */
	public function um_profile_tabs_active_cache( $tabs ) {
		$cache = wp_cache_get( 'profile_tabs_active', 'ultimate-member' );
		if ( $cache && is_array( $cache ) ) {
			$tabs = $cache;
		}
		return $tabs;
	}
}
