<?php
/**
 * Optimize images.
 *
 * @package um_ext\um_optimize\core
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
	 */
	class Images {


		/**
		 * Class constructor
		 */
		public function __construct() {

			$profile_photo_caching = UM()->options()->get( 'um_optimize_profile_photo' );

			if ( 'allow' === $profile_photo_caching ) {
				add_filter( 'um_filter_avatar_cache_time', '__return_false' );
			} elseif ( 'smart' === $profile_photo_caching ) {
				add_filter( 'um_filter_avatar_cache_time', array( $this, 'avatar_cache_time' ), 10, 2 );
			}
		}

		/**
		 * Change Profile Photo cache time.
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
				$dir = UM()->uploader()->get_upload_base_dir();
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

	}

}