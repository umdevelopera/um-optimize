<?php
namespace um_ext\um_optimize\common;

defined( 'ABSPATH' ) || exit;
if ( class_exists( 'um_ext\um_optimize\common\AJAX' ) ) {
	return;
}

/**
 * AJAX handlers.
 *
 * Get an instance this way: UM()->Optimize()->ajax()
 *
 * @package um_ext\um_optimize\common
 */
class AJAX {


	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->member_directory();

		add_action( 'wp_ajax_um_optimize_profile_tab', array( $this, 'load_profile_tab' ) );
		add_action( 'wp_ajax_nopriv_um_optimize_profile_tab', array( $this, 'load_profile_tab' ) );
	}

	/**
	 * Load classes needed to build the profile tab content.
	 *
	 * @param string $nav Profile tab slug.
	 */
	public function enable_profile_integrations( $nav ) {

		if ( 'activity' === $nav && is_plugin_active( 'um-social-activity/um-social-activity.php' ) ) {
			UM()->Activity_API();
		}

		if ( 'badges' === $nav && is_plugin_active( 'um-mycred/um-mycred.php' ) ) {
			UM()->myCRED()->frontend()->profile();
		}

		if ( 'bookmarks' === $nav && is_plugin_active( 'um-user-bookmarks/um-user-bookmarks.php' ) ) {
			UM()->User_Bookmarks()->frontend()->profile();
		}

		if ( ( 'followers' === $nav || 'following' === $nav ) && is_plugin_active( 'um-followers/um-followers.php' ) ) {
			UM()->Followers()->frontend()->profile();
		}

		if ( 'forums' === $nav && is_plugin_active( 'um-bbpress/um-bbpress.php' ) ) {
			UM()->bbPress()->frontend()->profile();
		}

		if ( 'friends' === $nav && is_plugin_active( 'um-friends/um-friends.php' ) ) {
			UM()->Friends()->frontend()->profile();
		}

		if ( 'groups_list' === $nav && is_plugin_active( 'um-groups/um-groups.php' ) ) {
			if ( UM()->is_new_ui() ) {
				UM()->Groups()->frontend()->profile();
			}
		}

		if ( 'messages' === $nav && is_plugin_active( 'um-messaging/um-messaging.php' ) ) {
			add_filter( 'um_get_template', function( $located, $template_name, $path ) {
				if ( 'js/conversations.php' === $template_name ) {
					$located = UM_OPTIMIZE_PATH . 'templates/empty.php';
				}
				return $located;
			}, 10, 3 );
		}

		if ( 'notes' === $nav && is_plugin_active( 'um-user-notes/um-user-notes.php' ) ) {
			UM()->User_Notes()->frontend()->profile();
		}

		if ( 'private_content' === $nav && is_plugin_active( 'um-private-content/um-private-content.php' ) ) {
			UM()->Private_Content()->frontend()->profile();
			add_action( 'the_post', function( $post ) {
				if ( $post instanceof \WP_Post && empty( $GLOBALS['post'] ) ) {
					$GLOBALS['post'] = $post;
				}
			} );
		}

		if ( 'reviews' === $nav && is_plugin_active( 'um-reviews/um-reviews.php' ) ) {
			if ( UM()->is_new_ui() ) {
				UM()->Reviews()->frontend()->profile();
			}
		}

		if ( 'photos' === $nav && is_plugin_active( 'um-user-photos/um-user-photos.php' ) ) {
			UM()->User_Photos()->frontend()->profile();
		}

//		if ( 'forumwp' === $nav && is_plugin_active( 'um-forumwp/um-forumwp.php' ) ) {
//			FMWP()->frontend()->shortcodes();
//			UM()->ForumWP()->frontend()->profile();
//		}
//		if ( 'jobboardwp_dashboard' === $nav && is_plugin_active( 'um-jobboardwp/um-jobboardwp.php' ) ) {
//			JB()->frontend()->shortcodes();
//			UM()->JobBoardWP()->frontend()->profile();
//		}
	}


	/**
	 * Dynamic load profile tab content.
	 */
	public function load_profile_tab() {
		UM()->check_ajax_nonce();

		if ( ! UM()->options()->get( 'profile_menu' ) ) {
			wp_die( 'Profile menu is disabled.' );
		}

		$input = map_deep( wp_unslash( $_REQUEST ), 'sanitize_text_field' );
		if ( empty( $input['user_id'] ) || empty( $input['form_id'] ) ) {
			wp_die( 'Unable to access the profile tab for an unknown profile.' );
		}

		// Set user.
		um_set_requested_user( (int) $input['user_id'] );
		um_fetch_user( (int) $input['user_id'] );

		$nav    = sanitize_key( $input['nav'] );
		$subnav = empty( $input['subnav'] ) ? 'default' : sanitize_key( $input['subnav'] );

		$this->enable_profile_integrations( $nav );
		$profile_tabs = UM()->profile()->tabs_active();
		if ( empty( $profile_tabs[ $nav ] ) ) {
			wp_die( 'Unable to access the inactive profile tab.' );
		}

		// Set profile data.
		$args = UM()->query()->post_data( (int) $input['form_id'] );
		$args = apply_filters( 'um_pre_args_setup', $args );
		$args = apply_filters( 'um_shortcode_args_filter', $args );

		ob_start();

		// Render profile tab content.
		?>
		<div class="um-profile-body <?php echo esc_attr( $nav . ' ' . $nav . '-' . $subnav ); ?>">
			<?php
			/**
			 * Custom hook to display tabbed content.
			 * These actions are documented in ultimate-member/templates/profile.php
			 */
			do_action( "um_profile_content_{$nav}", $args );
			do_action( "um_profile_content_{$nav}_{$subnav}", $args );
			?>
			<div class="clear"></div>
		</div>
		<?php

		// Render profile tab submenu.
		if ( ! empty( $profile_tabs[ $nav ]['subnav'] ) ) {
			$profile_link     = um_user_profile_url( $input['user_id'] );
			$profile_nav_link = add_query_arg( 'profiletab', $nav, $profile_link );
			$active_subnav    = 'default' === $subnav ? $profile_tabs[ $nav ]['subnav_default'] : $subnav;
			?>
			<div class="um-profile-subnav">
				<?php
				foreach ( $profile_tabs[ $nav ]['subnav'] as $slug => $subtab ) {
					$profile_subnav_link = add_query_arg( 'subnav', $slug, $profile_nav_link );
					$link                = apply_filters( 'um_user_profile_subnav_link', $profile_subnav_link, $slug, $subtab );

					$classes = array( 'um-profile-subnav-' . $slug . '-link' );
					if ( $active_subnav === $slug ) {
						$classes[] = 'active';
					}
					?>
					<a href="<?php echo esc_url( $link ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
						<?php echo wp_kses_post( $subtab ); ?>
					</a>
					<?php
				}
				?>
			</div>
			<?php
		}

		$content = ob_get_clean();
		exit( wp_kses( $content, UM()->get_allowed_html('templates') ) );
	}


	/**
	 * Optimize member directories.
	 *
	 * @return \um_ext\um_optimize\common\Member_Directory
	 */
	public function member_directory() {
		if ( empty( UM()->classes['um_optimize_member_directory'] ) ) {
			require_once UM_OPTIMIZE_PATH . 'includes/common/class-member-directory.php';
			UM()->classes['um_optimize_member_directory'] = new Member_Directory();
		}
		return UM()->classes['um_optimize_member_directory'];
	}
}
