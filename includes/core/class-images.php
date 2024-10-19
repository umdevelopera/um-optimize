<?php
/**
 * Optimize images.
 */

namespace um_ext\um_optimize\core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'um_ext\um_optimize\core\Images' ) ) {

	/**
	 * Class Images
	 *
	 * How to get an instance:
	 *  UM()->classes['um_optimize_images']
	 *  UM()->Optimize()->images()
	 *
	 * @package um_ext\um_optimize\core
	 */
	class Images {


		/**
		 * Class constructor
		 */
		public function __construct() {

			$profile_photo_caching = UM()->options()->get( 'um_optimize_profile_photo' );
			if ( $profile_photo_caching ) {
				add_filter( 'um_filter_avatar_cache_time', array( $this, 'avatar_cache_time' ), 10, 2 );
			}

			$cover_photo_caching = UM()->options()->get( 'um_optimize_cover_photo' );
			if ( $cover_photo_caching ) {
				add_filter( 'um_user_cover_photo_uri__filter', array( $this, 'cover_photo_uri' ), 10, 3 );
			}

			$cover_photo_size = UM()->options()->get( 'um_optimize_cover_photo_size' );
			if ( $cover_photo_size ) {
				add_action( 'um_members_after_user_name', array( $this, 'cover_photo_size' ), 10, 2 );
			}
		}


		/**
		 * Change Profile Photo cache time.
		 *
		 * Added to the filter hook `um_filter_avatar_cache_time`.
		 *
		 * @see um_get_avatar_uri()
		 *
		 * @since 1.2.0
		 *
		 * @param int $cache_time Avatar cache time.
		 * @param int $user_id    User ID.
		 * @return int
		 */
		public function avatar_cache_time( $cache_time, $user_id ) {
			$image = um_profile( 'profile_photo' );
			if ( $image ) {
				$dir = wp_normalize_path( UM()->uploader()->get_upload_base_dir() );
				if ( is_multisite() ) {
					$blog_id = get_current_blog_id();
					$dir     = str_replace( wp_normalize_path( "/sites/$blog_id/" ), '/', $dir );
				}
				$filename = wp_normalize_path( "$dir/$user_id/$image" );
				$filetime = filemtime( $filename );
				if ( $filetime ) {
					$cache_time = $filetime;
				}
			}
			return $cache_time;
		}


		/**
		 * Change Cover Photo cache time.
		 *
		 * Added to the filter hook `um_user_cover_photo_uri__filter`.
		 *
		 * @see um_user()
		 *
		 * @since 1.2.0
		 *
		 * @param string $cover_uri  Cover photo URL.
		 * @param bool   $is_default Default or not.
		 * @param array  $attrs      Attributes.
		 * @return string
		 */
		public function cover_photo_uri( $cover_uri, $is_default, $attrs ) {
			if ( ! $is_default ) {
				$image   = um_profile( 'cover_photo' );
				$user_id = um_profile( 'ID' );
				if ( $image && $user_id ) {
					$dir = wp_normalize_path( UM()->uploader()->get_upload_base_dir() );
					if ( is_multisite() ) {
						$blog_id = get_current_blog_id();
						$dir     = str_replace( wp_normalize_path( "/sites/$blog_id/" ), '/', $dir );
					}
					$filename = wp_normalize_path( "$dir/$user_id/$image" );
					$filetime = filemtime( $filename );
					if ( $filetime ) {
						$cover_url = current( explode( '?', $cover_uri ) );
						$cover_uri = "$cover_url?$filetime";
					}
				}
			}
			return $cover_uri;
		}


		/**
		 * Change Cover Photo size for the member directory.
		 *
		 * Added to the action hook `um_members_after_user_name`.
		 *
		 * @see \um\core\Member_Directory::build_user_card_data()
		 *
		 * @since 1.2.0
		 *
		 * @param int   $user_id        User ID.
		 * @param array $directory_data Directory settings.
		 */
		public function cover_photo_size( $user_id, $directory_data ) {
			if ( ! UM()->mobile()->isTablet() ) {
				UM()->member_directory()->cover_size = UM()->options()->get( 'um_optimize_cover_photo_size' );
			}
		}

	}

}